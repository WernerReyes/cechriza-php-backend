<?php
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/models/MenuModel.php";
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
class MenuService
{

    public function getAll()
    {
        $menus = MenuModel::with('children')->whereNull('parent_id')->orderBy('order')->get();
        return $menus;
    }

    public function countAll()
    {
        return MenuModel::count();
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
            if (!empty($orderMenu) && $orderMenu->id_menu != $menu->id_menu) {
                throw AppException::badRequest("Ya existe un menu con el orden $dto->order.");
            }



            Capsule::connection()->transaction(function () use ($dto, $menu) {
                $menu->update($dto->toUpdateDB());

                if ($dto->menuType === MenuTypes::INTERNAL_PAGE->value) {
                    $this->validateInternalPageMenu($dto->pageId);

                    //* Si tiene dropdown, eliminar las paginas hijas
                    if (!empty($menu->children) && count($menu->children) > 0) {
                        $menu->children->each(function ($child) {
                            PageModel::where('id_pages', $child->page->id_pages)
                                ->update(['menu_id' => null]);
                        });

                        MenuModel::where('parent_id', $menu->id_menu)->delete();
                    }

                    PageModel::where('id_pages', $dto->pageId)->update(['menu_id' => $menu->id_menu]);
                }

                if ($dto->menuType === MenuTypes::DROPDOWN->value) {
                    // $children = $this->validateAndCreateDropdownMenu($dto, $menu->id_menu);

                    error_log("Children count: " . count($menu->children) . " DTO count: " . count($dto->dropdownArray));

                    if (count($menu->children) > count($dto->dropdownArray)) {
                        //* Eliminamos la pagina que no esten
                        $childrenToDelete = $menu->children->filter(function ($child) use ($dto) {
                            return !collect($dto->dropdownArray)->pluck('menuId')->contains($child->id_menu);
                        });

                        if (!$childrenToDelete->isEmpty()) {

                            $childrenToDelete->each(function ($child) {
                                if (!isset($child['menuId'])) {
                                    return;
                                }

                                if ($child['menuType'] === MenuTypes::INTERNAL_PAGE->value) {
                                    PageModel::where('id_pages', $child['pageId'])
                                        ->update(['menu_id' => null]);
                                    return;
                                }


                            });

                            // Eliminar las páginas que no están en dropdownArray
                            MenuModel::whereIn('id_menu', $childrenToDelete->pluck('id_menu'))->delete();
                        }

                        //* Actualizamos los elementos existentes en el dropdown
                        $$menu->children = $this->validateAndUpdateDropdownMenu($dto, $menu->id_menu);

                    } else if (count($menu->children) < count($dto->dropdownArray)) {
                        // Agregar nuevos elementos en el dropdown
                        $newDropdownItems = array_filter(
                            $dto->dropdownArray,
                            fn($d) =>
                            $d["menuId"] === 0
                        );

                        $oldDropdownItems = array_filter(
                            $dto->dropdownArray,
                            fn($d) =>
                            $d["menuId"] != 0
                        );

                        if (!empty($oldDropdownItems)) {
                            $editOld = new UpdateMenuRequestDto([
                                "title" => $dto->title,
                                "order" => $dto->order,
                                "menuType" => MenuTypes::DROPDOWN->value,
                                "dropdownArray" => array_values($oldDropdownItems)
                            ], $menu->id_menu);

                            //* Actualizamos los elementos existentes en el dropdown
                            $menu->children = $this->validateAndUpdateDropdownMenu($editOld, $menu->id_menu);
                        }

                        if (!empty($newDropdownItems)) {
                            $newDto = new CreateMenuRequestDto([
                                "title" => $dto->title,
                                "order" => $dto->order,
                                "menuType" => MenuTypes::DROPDOWN->value,
                                "dropdownArray" => array_values($newDropdownItems)
                            ]);


                            $children = $this->validateAndCreateDropdownMenu($newDto, $menu->id_menu);
                            if (!empty($children)) {
                                $menu->children = $children;
                            }
                        }

                        // //* Actualizamos los elementos existentes en el dropdown
                        // $menu->children = $this->validateAndUpdateDropdownMenu($dto, $menu->id_menu);
                    } else {
                        //* Actualizamos los elementos existentes en el dropdown
                        $menu->children = $this->validateAndUpdateDropdownMenu($dto, $menu->id_menu);
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

            error_log($e->getMessage());

            throw new DBExceptionHandler($e, [
                ["name" => "unique_order_per_parent", "message" => "No puede haber dos menús con el mismo orden."],
            ]);
        }
    }


    private function validateAndUpdateDropdownMenu(UpdateMenuRequestDto $dto, int $parentId)
    {
        // $childrenData = [];
        $pageToMenuMapping = []; // ✅ Mapear cada página con su menú correspondiente


        Capsule::connection()->transaction(function () use ($dto, $parentId) {
            // 1. Fase temporal: mover órdenes fuera del rango válido
            foreach ($dto->dropdownArray as $index => $dropdownItem) {
                $dropdownDto = new UpdateMenuRequestDto($dropdownItem, $dropdownItem['menuId']);
                $dropdownDto = $dropdownDto->validate();

                if (is_array($dropdownDto)) {
                    throw AppException::validationError("Validation failed", $dropdownDto);
                }

                $child = MenuModel::find($dropdownDto->menuId);
                if (!$child) {
                    throw AppException::notFound("No existe un menú con el ID proporcionado");
                }

                error_log("child found: " . $index . " " . $index + 1);


                // Asignar orden temporal, ejemplo: negativo para evitar duplicados
                $child->update(['order' => -($index + 1)]);
            }

            // 2. Fase final: ya puedes poner los órdenes correctos
            foreach ($dto->dropdownArray as $index => $dropdownItem) {
                $dropdownDto = new UpdateMenuRequestDto($dropdownItem, $dropdownItem['menuId']);
                $dropdownDto->parentId = $parentId;


                $data = $dropdownDto->toUpdateDB();
                unset($data["pages_id"]);


                error_log("Updating child id_menu: " . json_encode($data) . " json: " . json_encode($dropdownDto));



                // $child->update($data);
                MenuModel::where('id_menu', $dropdownDto->menuId)->update($data);

                // ✅ Guardar mapeo de página a índice para vincular después
                if ($dropdownDto->menuType === MenuTypes::INTERNAL_PAGE->value) {
                    $pageToMenuMapping[$index] = $dropdownDto->pageId;
                }
            }
        });

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

    public function delete(int $id, string $type): void
    {

        $menu = $this->findMenuById($id);

        error_log('Deleting menu: ' . $type);

        if ($type === MenuTypes::INTERNAL_PAGE->value) {

            if (!empty($menu->page)) {
                PageModel::where('id_pages', $menu->page->id_pages)
                    ->update(['menu_id' => null]);
            }

        } else if ($type === MenuTypes::DROPDOWN->value) {

            if (!empty($menu->children) && count($menu->children) > 0) {


                $menu->children->each(function ($child) {
                    if (!empty($child->page)) {

                        PageModel::where('id_pages', $child->page->id_pages)
                            ->update(['menu_id' => null]);
                    }
                });

                MenuModel::where('parent_id', $menu->id_menu)->delete();
            }
        }

        $menu->delete();

    }

}