<?php
require_once "app/models/SectionModel.php";
require_once "app/models/PageModel.php";
require_once "app/models/PageSectionModel.php";
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/dtos/section/request/CreateSectionRequestDto.php";
require_once "app/dtos/section/response/SectionResponseDto.php";
use Illuminate\Database\Capsule\Manager as Capsule;

class SectionService
{

    private FileUploader $fileUploader;

    public function __construct()
    {
        $this->fileUploader = new FileUploader();
    }
    public function getAll()
    {
        $sections = SectionModel::with([
            'sectionItems',
            'link:id_link,type,title',
            'extraLink:id_link,type,title',
            'sectionItems.link:id_link,type',

            'pages:id_page,title,slug',
            'pivot',
            'machines:id_machine,name,images,description,category_id,long_description,technical_specifications,manual,link_id,text_button',
            'machines.category:id_category,title,type',

            'menus.parent.parent:id_menu,title',

        ])->get();



        return $sections->map(fn($section) => new SectionResponseDto($section));
    }


    public function create(CreateSectionRequestDto $dto)
    {
        try {
            // $maxOrder = SectionModel::max('order_num') ?? 0;

            $imageUrl = null;
            $fileIconUrl = null;
            $fileVideoUrl = null;
            if (
                $this->allowSectionTypeToUpsertImages($dto->type)
            ) {
                $imageUrl = $this->getImageToInsertDB($dto->imageUrl, $dto->fileImage);

            }

            if (
                in_array($dto->type, [
                    SectionType::FULL_MAINTENANCE_PLAN->value,
                    SectionType::SUPPORT_WIDGET->value
                ])
            ) {
                if ($dto->iconType == IconType::IMAGE->value) {
                    $fileIconUrl = $this->getImageToInsertDB($dto->fileIconUrl, $dto->fileIcon);
                }
            }

            if (
                in_array($dto->type, [
                    SectionType::PREVENTIVE_CORRECTIVE_MAINTENANCE->value,
                ])
            ) {
                $fileVideoUrl = $this->getVideoToInsertDB($dto->fileVideo);
            }

            $section = SectionModel::create($dto->toInsertDB($imageUrl, $fileIconUrl, $fileVideoUrl));

            if (in_array($dto->type, [SectionType::MAIN_NAVIGATION_MENU->value, SectionType::FOOTER->value]) && !empty($dto->menusIds)) {
                foreach ($dto->menusIds as $index => $menuId) {
                    $section->menus()->attach($menuId, [
                        'order_num' => $index + 1
                    ]);
                }

                $section->load('menus:id_menu,title,parent_id', 'menus.parent:id_menu,title,parent_id', 'menus.parent.parent:id_menu,title');
            }

            if (in_array($dto->type, [SectionType::MACHINE->value, SectionType::MACHINE_DETAILS->value, SectionType::MACHINES_CATALOG->value, SectionType::CASH_PROCESSING_EQUIPMENT->value]) && !empty($dto->machinesIds)) {
                $section->machines()->attach($dto->machinesIds);

                $section->load('machines:id_machine,name,images,description,category_id,long_description,technical_specifications,manual,link_id,text_button', 'machines.category:id_category,title,type');
            }

            // Asociar la sección a una página específica con orden
            if ($dto->pageId) {
                $maxOrder = PageSectionModel::where('id_page', $dto->pageId)->max('order_num') ?? 0;
                $section->pages()->attach($dto->pageId, [
                    'order_num' => $maxOrder + 1,
                    'active' => $dto->active,
                    'type' => $dto->mode
                ]);

                $section->load('pivot:id_page,id_section,order_num,active,type');
            }

            if ($dto->linkId) {
                $section->load('link:id_link,type,title');
            }

            if ($dto->extraLinkId) {
                $section->load('extraLink:id_link,type,title');
            }


            return new SectionResponseDto($section);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function update(UpdateSectionRequestDto $dto)
    {
        $section = SectionModel::with('pivot')->find($dto->id);
        if (empty($section)) {
            throw AppException::validationError("La sección seleccionada no existe");
        }
        $imageUrl = null;
        $fileIconUrl = null;
        $fileVideoUrl = null;

        if ($this->allowSectionTypeToUpsertImages($dto->type)) {
            $imageUrl = $this->getImageToUpdateDB($section->image, $dto->currentImageUrl, $dto->imageUrl, $dto->fileImage);
        }

        if (
            in_array($dto->type, [
                SectionType::FULL_MAINTENANCE_PLAN->value,
                SectionType::SUPPORT_WIDGET->value
            ])
        ) {
            if ($dto->iconType == IconType::IMAGE->value) {
                $fileIconUrl = $this->getImageToUpdateDB($section->icon, $dto->fileIconUrl, null, $dto->fileIcon);
            } elseif ($dto->iconType == IconType::LIBRARY->value) {
                // If switching to library icon, delete existing image icon if any
                if ($section->icon_url) {
                    $this->fileUploader->deleteImage($section->icon_url);
                }

                $fileIconUrl = null;
            }
        }

        if (
            in_array($dto->type, [
                SectionType::PREVENTIVE_CORRECTIVE_MAINTENANCE->value,
            ])
        ) {
            $fileVideoUrl = $this->getVideoToUpdateDB($section->video, $dto->currentVideoUrl, $dto->fileVideo);
        }

        // error_log("Image URL to update: " . $imageUrl);


        $section->update($dto->toUpdateDB($imageUrl, $fileIconUrl, $fileVideoUrl));

        if ($dto->active !== null && $dto->pageId !== null) {
            PageSectionModel::where('id_page', $dto->pageId)
                ->where('id_section', $dto->id)
                ->update([
                    'active' => $dto->active,
                ]);
            $section->load('pivot');
        }

        if (in_array($dto->type, [SectionType::MAIN_NAVIGATION_MENU->value, SectionType::FOOTER->value]) && !empty($dto->menusIds)) {
            $menusSync = [];

            // Si existen menús asociados en el request
            if (!empty($dto->menusIds)) {
                foreach ($dto->menusIds as $index => $menuId) {
                    $menusSync[$menuId] = ['order_num' => $index + 1];
                }
            }

            // Esta línea asocia solo los actuales y desasocia los que faltan
            $section->menus()->sync($menusSync);

            $section->load('menus:id_menu,title,parent_id', 'menus.parent:id_menu,title,parent_id', 'menus.parent.parent:id_menu,title');
        }

        if (in_array($dto->type, [SectionType::MACHINE->value, SectionType::MACHINE_DETAILS->value, SectionType::MACHINES_CATALOG->value, SectionType::CASH_PROCESSING_EQUIPMENT->value]) && !empty($dto->machinesIds)) {
            $section->machines()->sync($dto->machinesIds);

            $section->load('machines:id_machine,name,images,description,category_id,long_description,technical_specifications,manual,link_id,text_button', 'machines.category:id_category,title,type');
        }

        // if ($section->pivotPages) {
        //     $section->load('pivotPages:id_page,id_section,order_num,active,type');
        // }

        if ($dto->linkId) {
            $section->load('link:id_link,type,title');
        }

        if ($dto->extraLinkId) {
            $section->load('extraLink:id_link,type,title');
        }

        return new SectionResponseDto($section);
    }

    private function allowSectionTypeToUpsertImages(string $type)
    {
        return in_array($type, [
            SectionType::OUR_COMPANY->value,
            SectionType::MAIN_NAVIGATION_MENU->value,
            SectionType::CTA_BANNER->value,
            SectionType::MISSION_VISION->value,
            SectionType::CONTACT_US->value,
            SectionType::FULL_MAINTENANCE_PLAN->value,
            SectionType::FOOTER->value,
            SectionType::PREVENTIVE_CORRECTIVE_MAINTENANCE->value,
            SectionType::SUPPORT_WIDGET->value,
        ]);
    }

    public function associeteToPages(AssocieteToPagesRequestDto $dto)
    {
        $section = SectionModel::with('pivot')->find($dto->id);

        if (empty($section)) {
            throw AppException::badRequest("La sección seleccionada no existe");
        }

        $pages = PageModel::whereIn('id_page', $dto->pagesIds)->get();
        if ($pages->count() !== count($dto->pagesIds)) {
            throw AppException::badRequest("Una o más páginas no existen con los IDs proporcionados.");
        }

        // IDs de páginas actuales
        $currentPageIds = $section->pivot->pluck('id_page')->toArray();

        // IDs nuevos que vienen del front
        $newPageIds = $dto->pagesIds;

        // ➕ Páginas a agregar
        $toAttach = array_diff($newPageIds, $currentPageIds);

        // ➖ Páginas a eliminar
        $toDetach = array_diff($currentPageIds, $newPageIds);


        // Eliminar las que ya no deben estar
        if (!empty($toDetach)) {
            PageSectionModel::where('id_section', $section->id_section)
                ->whereIn('id_page', $toDetach)
                ->delete();
        }

        // Agregar nuevas con el siguiente orden por cada página
        foreach ($toAttach as $pageId) {
            $maxOrder = PageSectionModel::where('id_page', $pageId)->max('order_num') ?? 0;

            PageSectionModel::create([
                'id_page' => $pageId,
                'id_section' => $section->id_section,
                'order_num' => $maxOrder + 1,
                'type' => SectionMode::LAYOUT->value,
                'active' => 1,
            ]);
        }

        // Recargar pivote actualizado
        $section->load('pivot');
        $section->load('pages:id_page');

        return new SectionResponseDto($section);
    }



    public function updateOrder(UpdateOrderRequestDto $dto)
    {

        $sections = SectionModel::whereIn('id_section', array_column($dto->orderArray, 'id'))->get();
        if (count($sections) !== count(value: $dto->orderArray)) {
            throw AppException::badRequest("Una o más secciones no existen con los IDs proporcionados.");
        }

        error_log("Updating order for sections: " . json_encode($dto->orderArray));

        Capsule::connection()->transaction(function () use ($dto) {
            foreach ($dto->orderArray as $item) {
                // SectionModel::where('id_section', $item['id'])->update([
                //     'order_num' => $item['order'],
                // ]);
                PageSectionModel::where('id_section', $item['id'])
                    ->where('id_page', $item['pageId'])
                    ->update([
                        'order_num' => $item['order'],
                    ]);
            }
        });

    }

    public function delete(int $id, $pageId): void
    {
        try {
            $section = SectionModel::with('sectionItems', 'pivot')->find($id);
            if (empty($section)) {
                throw AppException::notFound("La sección seleccionada no existe");
            }


            Capsule::connection()->transaction(function () use ($section, $pageId) {


                // 🔹 Caso 1: Eliminar solo la asociación con una página específica
                if ($pageId) {
                    // Validar que la sección esté asociada realmente a esa página
                    $isLinked = $section->pages->contains('id_page', $pageId);
                    if (!$isLinked) {
                        throw AppException::notFound("La sección no está asociada con la página indicada.");
                    }

                    $section->pages()->detach($pageId);


                    return;
                }

                // 🔹 Caso 2: Eliminar completamente la sección
                //* Delete all section items
                SectionItemModel::where('section_id', $section->id_section)->delete();

                //* First, delete all images associated with section items
                foreach ($section->sectionItems as $item) {
                    if ($item->image) {
                        $this->fileUploader->deleteImage($item->image);
                    }
                }

                //* Then, delete the section image if exists
                if ($section->image) {
                    $this->fileUploader->deleteImage($section->image);
                }

                $section->delete();
            });
        } catch (Exception $e) {
            if (get_class($e) === "AppException") {
                throw $e;
            }
            throw new DBExceptionHandler($e, [
                ["name" => "fk_section_items_section", "message" => "No se puede eliminar la sección porque está asociada a uno o más ítems de sección"],
                ["name" => "fk_menus_section", "message" => "No se puede eliminar la sección porque está asociada a uno o más menús"]
            ]);
        }
    }

    private function getVideoToUpdateDB($videoDB, $currentVideoUrl, $fileVideo)
    {
        if (empty($fileVideo) && empty($currentVideoUrl)) {
            return null;
        } else if (empty($fileVideo) && !empty($currentVideoUrl)) {
            return $this->fileUploader->getPathFromUrl($currentVideoUrl);
        }

        if ($fileVideo && $videoDB) {
            $this->fileUploader->deleteVideo($videoDB);
        }

        return $this->getVideoToInsertDB($fileVideo);

    }


    private function getVideoToInsertDB($fileVideo)
    {
        $currentVideoUrl = null;
        if (!empty($fileVideo)) {
            $uploadResult = $this->fileUploader->uploadVideo($fileVideo);

            if (isset($uploadResult["error"])) {
                throw AppException::validationError("La subida del video falló, intentalo de nuevo" . $uploadResult['error']);
            }

            $currentVideoUrl = $uploadResult['path'];
        }

        return $currentVideoUrl;
    }


    private function getImageToInsertDB($imageUrl, $fileImage)
    {
        $currentImageUrl = null;
        if (!empty($fileImage)) {
            $uploadResult = $this->fileUploader->uploadImage($fileImage);

            if (isset($uploadResult["error"])) {
                throw AppException::validationError("La subida de la imagen falló, intentalo de nuevo" . $uploadResult['error']);
            }

            $currentImageUrl = $uploadResult['path'];
        }

        if (!empty($imageUrl)) {
            $uploadResult = $this->fileUploader->uploadImageFromUrl($imageUrl);
            if (isset($uploadResult["error"])) {
                throw AppException::internalServer($uploadResult["error"], $uploadResult["error"]);
            }

            $currentImageUrl = $uploadResult['path'];
        }

        return $currentImageUrl;
    }

    private function getImageToUpdateDB($imageDB, $currentImageUrl, $newImageUrl, $fileImage)
    {

        if (empty($newImageUrl) && empty($fileImage) && empty($currentImageUrl)) {
            return null;
        } else if (empty($newImageUrl) && empty($fileImage) && !empty($currentImageUrl)) {
            return $this->fileUploader->getPathFromUrl($currentImageUrl);
        }

        if ($fileImage && $imageDB) {
            $this->fileUploader->deleteImage($imageDB);
        }

        return $this->getImageToInsertDB($newImageUrl, $fileImage);

    }
}