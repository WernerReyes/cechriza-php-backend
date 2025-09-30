<?php
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/models/MenuModel.php";
require_once "app/entities/MenuEntity.php";
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
class MenuService
{

    public function getAll()
    {
        $menus = MenuModel::with('children')->whereNull('parent_id')->orderBy('order')->get();
        return $menus;
    }

    public function findMenuById($id)
    {
        $menu = MenuModel::with(['page', 'children.page'])->find($id);
        if (empty($menu)) {
            throw AppException::notFound("No existe un menú con el ID proporcionado");
        }

        return $menu;
    }

    public function create(CreateMenuRequestDto $dto)
    {
        try {
            if ($dto->menuType === MenuTypes::INTERNAL_PAGE->value) {
                $this->validateInternalPageMenu($dto->pageId);
            }

            $orderMenu = MenuModel::where('order', $dto->order)->where('parent_id', null)->first();
            if (!empty($orderMenu)) {
                throw AppException::badRequest("Ya existe un menu con el orden $dto->order.");
            }

            $menu = Capsule::connection()->transaction(function () use ($dto) {
                $menu = MenuModel::create($dto->toInsertDB());

                if ($dto->menuType === MenuTypes::INTERNAL_PAGE->value) {
                    PageModel::where('id_pages', $dto->pageId)->update(['menu_id' => $menu->id_menu]);
                }

                if ($dto->menuType === MenuTypes::DROPDOWN->value) {
                    $children = $this->validateAndCreateDropdownMenu($dto, $menu->id_menu);
                    if (!empty($children)) {
                        $menu->children = $children;
                    }
                }

                return $menu;
            });


            return $menu;
        } catch (Exception $e) {
            if ($e instanceof AppException) {
                throw $e;
            }

            // DB::rollBack();



            throw new DBExceptionHandler($e, [
                ["name" => "unique_order_per_parent", "message" => "No puede haber dos menús con el mismo orden."],
                ["name" => "order_unique", "message" => "Ya existe un menu con el orden $dto->order."],
            ]);
        }

    }

    private function validateInternalPageMenu(int $pageId)
    {
        $page = PageModel::find($pageId);
        if (empty($page)) {
            throw AppException::notFound("No existe una página con el ID proporcionado");
        }
    }

    // filepath: c:\xampp\htdocs\api\app\services\MenuService.php

    private function validateAndCreateDropdownMenu(CreateMenuRequestDto $dto, int $parentId)
    {
        $childrenData = [];
        $pageToMenuMapping = []; // ✅ Mapear cada página con su menú correspondiente

        foreach ($dto->dropdownArray as $index => $dropdownItem) {
            $dropdownDto = new CreateMenuRequestDto($dropdownItem);
            $dropdownDto = $dropdownDto->validate();

            if (is_array($dropdownDto)) {
                throw AppException::validationError("Validation failed", $dropdownDto);
            }

            $dropdownDto->parentId = $parentId;
            $data = $dropdownDto->toInsertDB();
            unset($data["pages_id"]);

            $childrenData[] = $data;

            // ✅ Guardar mapeo de página a índice para vincular después
            if ($dropdownDto->menuType === MenuTypes::INTERNAL_PAGE->value) {
                $pageToMenuMapping[$index] = $dropdownDto->pageId;
            }
        }


        // ✅ Insertar menús hijos
        MenuModel::insert($childrenData);

        // ✅ Obtener los menús recién creados (con sus IDs)
        $children = MenuModel::where('parent_id', $parentId)
            ->orderBy('order')
            ->get();


        // ✅ CORRECCIÓN: Vincular cada página con su menú hijo específico
        if (!empty($pageToMenuMapping)) {
            foreach ($pageToMenuMapping as $childIndex => $pageId) {
                // ✅ Obtener el ID del menú hijo correspondiente por índice
                if (isset($children[$childIndex])) {
                    $childMenuId = $children[$childIndex]->id_menu;

                    // ✅ Actualizar la página específica con el ID del menú hijo
                    PageModel::where('id_pages', $pageId)
                        ->update(['menu_id' => $childMenuId]);
                }
            }
        }

        return $children;
    }


    public function update(UpdateMenuRequestDto $dto)
    {
        try {
            $menu = $this->findMenuById($dto->menuId);

            if ($dto->menuType === MenuTypes::INTERNAL_PAGE->value) {
                $this->validateInternalPageMenu($dto->pageId);
            }

            $orderMenu = MenuModel::where('order', $dto->order)->where('parent_id', null)->first();
            if (!empty($orderMenu)) {
                throw AppException::badRequest("Ya existe un menu con el orden $dto->order.");
            }



            Capsule::connection()->transaction(function () use ($dto, $menu) {
                MenuModel::update($dto->toUpdateDB());

                if ($dto->menuType === MenuTypes::INTERNAL_PAGE->value) {
                    PageModel::where('id_pages', $dto->pageId)->update(['menu_id' => $menu->id_menu]);
                }

                if ($dto->menuType === MenuTypes::DROPDOWN->value) {
                    // $children = $this->validateAndCreateDropdownMenu($dto, $menu->id_menu);
                    if ($menu->children->length != $dto->dropdownArray->length) {
                       //* Eliminamos la pagina que no esten
                       $menu->children = $menu->children->filter(fn($child) =>
                        !collect($dto->dropdownArray)->contains(fn($d) => $d['pageId'] === $child->page_id)
                       );

                       // Eliminar las páginas que no están en dropdownArray
                       Model::whereIn('id_menu', $menu->children->pluck('id_menu'))->delete();
                    }

                    $children = $this->validateAndUpdateDropdownMenu($dto, $menu->id_menu);
                    if (!empty($children)) {
                        $menu->children = $children;
                    }
                } else {
                    if ($menu->children) {
                        // Eliminar las paginas hijas
                        MenuModel::where('parent_id', $menu->id_menu)->delete();
                    }
                }

                return $menu;
            });


            return $menu;
        } catch (Exception $e) {
            if ($e instanceof AppException) {
                throw $e;
            }

            throw new DBExceptionHandler($e, [
                ["name" => "unique_order_per_parent", "message" => "No puede haber dos menús con el mismo orden."],
            ]);
        }
    }


    private function validateAndUpdateDropdownMenu(UpdateMenuRequestDto $dto, int $parentId)
    {
        $childrenData = [];
        $pageToMenuMapping = []; // ✅ Mapear cada página con su menú correspondiente

        foreach ($dto->dropdownArray as $index => $dropdownItem) {
            $dropdownDto = new UpdateMenuRequestDto($dropdownItem, $parentId);
            $dropdownDto = $dropdownDto->validate();

            if (is_array($dropdownDto)) {
                throw AppException::validationError("Validation failed", $dropdownDto);
            }

            $dropdownDto->parentId = $parentId;
            $data = $dropdownDto->toUpdateDB();
            unset($data["pages_id"]);

            $childrenData[] = $data;

            // ✅ Guardar mapeo de página a índice para vincular después
            if ($dropdownDto->menuType === MenuTypes::INTERNAL_PAGE->value) {
                $pageToMenuMapping[$index] = $dropdownDto->pageId;
            }
        }


        // ✅ Insertar menús hijos
        MenuModel::update($childrenData);

        // ✅ Obtener los menús recién creados (con sus IDs)
        $children = MenuModel::where('parent_id', $parentId)
            ->orderBy('order')
            ->get();


        // ✅ CORRECCIÓN: Vincular cada página con su menú hijo específico
        if (!empty($pageToMenuMapping)) {
            foreach ($pageToMenuMapping as $childIndex => $pageId) {
                // ✅ Obtener el ID del menú hijo correspondiente por índice
                if (isset($children[$childIndex])) {
                    $childMenuId = $children[$childIndex]->id_menu;

                    // ✅ Actualizar la página específica con el ID del menú hijo
                    PageModel::where('id_pages', $pageId)
                        ->update(['menu_id' => $childMenuId]);
                }
            }
        }

        return $children;
        
    }

    public function delete(int $id): void
    {

        $menu = $this->findMenuById($id);

        if (!$menu->active) {
            throw AppException::badRequest("El menú ya está inactivo");
        }

        $deleted = MenuModel::update($menu->toArray(), ['active' => 0]);

        if (!$deleted) {
            throw AppException::badRequest("No se pudo eliminar el menú");
        }

    }

}