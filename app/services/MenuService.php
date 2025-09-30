<?php
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/models/MenuModel.php";
require_once "app/entities/MenuEntity.php";
class MenuService
{
    // private MenuModel $menuModel;
    // private PageModel $pageModel;

    // public function __construct()
    // {
    //     // $this->menuModel = MenuModel::getInstance();
    //     $this->pageModel = PageModel::getInstance();
    // }

    public function getAll()
    {
        $menus = MenuModel::all();
        return array_map(fn($menu) => new MenuEntity($menu), $menus->toArray());
    }

    public function findMenuById($id)
    {
        $menu = MenuModel::find($id);
        if (empty($menu)) {
            throw AppException::notFound("No existe un menú con el ID proporcionado");
        }

        return new MenuEntity($menu->toArray());
    }

    public function create(CreateMenuRequestDto $dto)
    {
        try {
            if ($dto->menuType === MenuTypes::INTERNAL_PAGE->value) {
                $this->validateInternalPageMenu($dto);
            }

            $orderMenu = MenuModel::where('order', $dto->order)->first();
            if (!empty($orderMenu)) {
                throw AppException::badRequest("Ya existe un menu con el orden $dto->order.");
            }

            $menu = MenuModel::create($dto->toInsertDB());

            if ($dto->menuType === MenuTypes::DROPDOWN->value) {
                error_log("Validating and creating dropdown menu items " . json_encode($dto->dropdownArray));
                $this->validateAndCreateDropdownMenu($dto, $menu->id_menu);
            }

            error_log("Created menu: " . json_encode($menu->toArray()));

            return $menu;
        } catch (Exception $e) {
            if ($e instanceof AppException) {
                throw $e;
            }



            throw new DBExceptionHandler($e, [
                ["name" => "unique_order_per_parent", "message" => "No puede haber dos menús con el mismo orden."],
                ["name" => "order_unique", "message" => "Ya existe un menu con el orden $dto->order."],
            ]);
        }

    }

    private function validateInternalPageMenu(CreateMenuRequestDto $dto)
    {
        $page = PageModel::find($dto->pageId);
        if (empty($page)) {
            throw AppException::notFound("No existe una página con el ID proporcionado");
        }
    }

    private function validateAndCreateDropdownMenu(CreateMenuRequestDto $dto, int $parentId)
    {


        foreach ($dto->dropdownArray as $dropdownItem) {
            $dropdownDto = new CreateMenuRequestDto($dropdownItem);
            $dropdownDto = $dropdownDto->validate();
            if (is_array($dropdownDto)) {
                throw AppException::validationError("Validation failed", $dropdownDto);
            }

            if ($dropdownDto->menuType === MenuTypes::INTERNAL_PAGE) {
                $this->validateInternalPageMenu($dropdownDto);
            }

            $dropdownDto->parentId = $parentId;

            error_log("Creating dropdown menu item " . json_encode($dropdownDto));
            MenuModel::create($dropdownDto->toInsertDB());
        }


    }

    public function update(UpdateMenuRequestDto $dto): MenuEntity
    {
        try {
            $menu = $this->findMenuById($dto->id);

            if ($dto->parentId) {
                $this->findMenuById(id: $dto->parentId);
            }

            $updateData = MenuModel::update($menu->toArray(), $dto->toUpdateDB());

            if (!$updateData) {
                throw AppException::badRequest("No se pudo actualizar el menú");
            }

            return new MenuEntity($updateData->toArray());
        } catch (Exception $e) {
            if ($e instanceof AppException) {
                throw $e;
            }

            throw new DBExceptionHandler($e, [
                ["name" => "unique_order_per_parent", "message" => "No puede haber dos menús con el mismo orden."],
            ]);
        }
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