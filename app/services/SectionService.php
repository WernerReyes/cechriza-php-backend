<?php
require_once "app/models/SectionModel.php";
require_once "app/models/PageModel.php";
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/dtos/section/request/CreateSectionRequestDto.php";
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
        return SectionModel::with([
            'sectionItems',
            'link:id_link,type',
            'menus' => function ($query) {
                $query->orderBy('menu.order_num', 'asc')
              ->select('menu.id_menu', 'menu.title', 'menu.parent_id', 'menu.order_num');
            },
            'menus.parent:id_menu,title,order_num'
        ])
            ->orderBy('order_num', 'asc') // ordena las secciones también
            ->get();

    }

    public function create(CreateSectionRequestDto $dto)
    {
        try {
            $maxOrder = SectionModel::max('order_num') ?? 0;

            $imageUrl = null;
            if ($dto->type === SectionType::OUR_COMPANY->value || $dto->type === SectionType::MAIN_NAVIGATION_MENU->value) {
                $imageUrl = $this->getImageToInsertDB($dto->imageUrl, $dto->fileImage);
            }

            $section = SectionModel::create(array_merge($dto->toInsertDB($imageUrl), ["order_num" => $maxOrder + 1]));

            if ($dto->type === SectionType::MAIN_NAVIGATION_MENU->value && !empty($dto->menusIds)) {
                $section->menus()->attach($dto->menusIds);

                $section->load('menus:id_menu,title,parent_id', 'menus.parent:id_menu,title');
            }

            return $section;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function update(UpdateSectionRequestDto $dto)
    {
        $section = SectionModel::find($dto->id);
        if (empty($section)) {
            throw AppException::validationError("La sección seleccionada no existe");
        }

        $imageUrl = null;
        if ($dto->type === SectionType::OUR_COMPANY->value || $dto->type === SectionType::MAIN_NAVIGATION_MENU->value) {
            $imageUrl = $this->getImageToUpdateDB($section->image, $dto->currentImageUrl, $dto->imageUrl, $dto->fileImage);
        }

        error_log("Image URL to update: " . $imageUrl);


        $section->update($dto->toUpdateDB($imageUrl));

        if ($dto->type === SectionType::MAIN_NAVIGATION_MENU->value && !empty($dto->menusIds)) {
            $section->menus()->sync($dto->menusIds);

            $section->load('menus:id_menu,title,parent_id', 'menus.parent:id_menu,title');
        }
        return $section;
    }

    public function updateOrder(UpdateOrderRequestDto $dto)
    {

        $sections = SectionModel::whereIn('id_section', array_column($dto->orderArray, 'id'))->get();
        if (count($sections) !== count($dto->orderArray)) {
            throw AppException::badRequest("Una o más secciones no existen con los IDs proporcionados.");
        }

        Capsule::connection()->transaction(function () use ($dto) {
            foreach ($dto->orderArray as $item) {
                SectionModel::where('id_section', $item['id'])->update([
                    'order_num' => $item['order'],
                ]);
            }
        });

    }


    private function getImageToInsertDB($imageUrl, $fileImage)
    {
        $currentImageUrl = $imageUrl;
        if (!empty($fileImage)) {
            $uploadResult = $this->fileUploader->uploadImage($fileImage);

            if (is_string($uploadResult)) {
                throw AppException::validationError("Image upload failed: " . $uploadResult);
            }

            $currentImageUrl = $uploadResult['url'];
        }

        return $currentImageUrl;
    }

    private function getImageToUpdateDB($imageDB, $currentImageUrl, $newImageUrl, $fileImage)
    {

        if ($imageDB) {
            $this->fileUploader->deleteImage($imageDB);
        }

        if (empty($newImageUrl) && empty($fileImage) && empty($currentImageUrl)) {
            return null;
        } else if (empty($newImageUrl) && empty($fileImage) && !empty($currentImageUrl)) {
            return $currentImageUrl;
        }

        return $this->getImageToInsertDB($newImageUrl, $fileImage);

    }
}