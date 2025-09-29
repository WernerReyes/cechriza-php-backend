<?php
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/models/MenuModel.php";
require_once "app/entities/MenuEntity.php";
class MenuService
{
    private MenuModel $menuModel;
    private PageModel $pageModel;

    public function __construct()
    {
        $this->menuModel = MenuModel::getInstance();
        $this->pageModel = PageModel::getInstance();
    }

    public function getAll()
    {
        $menus = $this->menuModel->getAll();
        return array_map(fn($menu) => new MenuEntity($menu), $menus);
    }

    public function findMenuById($id)
    {
        $menu = $this->menuModel->getByField(MenuSearchField::ID, $id);
        if (empty($menu)) {
            throw AppException::notFound("No existe un menú con el ID proporcionado");
        }

        return new MenuEntity($menu[0]);
    }

    public function create(CreateMenuRequestDto $dto): MenuEntity
    {
        try {
            if ($dto->menuType === MenuTypes::INTERNAL_PAGE->value) {
                $this->validateInternalPageMenu($dto);
            }

            $orderMenu = $this->menuModel->getByField(MenuSearchField::ORDER, $dto->order);
            if (!empty($orderMenu)) {
                throw AppException::badRequest("Ya existe un menu con el orden $dto->order.");
            }

            $menu = $this->menuModel->create($dto->toInsertDB());

            if ($dto->menuType === MenuTypes::DROPDOWN->value) {
                error_log("Validating and creating dropdown menu items " . json_encode($dto->dropdownArray));
                $this->validateAndCreateDropdownMenu($dto, $menu['id_menu']);
            }

            return new MenuEntity($menu);
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
        $page = $this->pageModel->getByField(PageSearchField::ID, $dto->pageId);
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
            $this->menuModel->create($dropdownDto->toInsertDB());
        }


    }

    public function update(UpdateMenuRequestDto $dto): MenuEntity
    {
        try {
            $this->findMenuById($dto->id);

            if ($dto->parentId) {
                $this->findMenuById(id: $dto->parentId);
            }

            $updateData = $this->menuModel->update($dto->toUpdateDB());

            if (!$updateData) {
                throw AppException::badRequest("No se pudo actualizar el menú");
            }

            return new MenuEntity($updateData);
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

        $deleted = $this->menuModel->delete($id);

        if (!$deleted) {
            throw AppException::badRequest("No se pudo eliminar el menú");
        }

    }

}