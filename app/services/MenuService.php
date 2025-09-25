<?php
require_once "app/exceptions/MenuExceptionHandler.php";
require_once "app/models/MenuModel.php";
require_once "app/entities/MenuEntity.php";
class MenuService
{
    private MenuModel $menuModel;

    public function __construct()
    {
        $this->menuModel = MenuModel::getInstance();
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

        error_log("ID del menú encontrado: " . json_encode($menu));

        return new MenuEntity($menu[0]);
    }

    public function create(CreateMenuRequestDto $dto): MenuEntity
    {
        try {
            if ($dto->parentId) {
                $this->findMenuById($dto->parentId);
            }

            $menu = $this->menuModel->create($dto->toInsertDB());
            return new MenuEntity($menu);
        } catch (Exception $e) {
            if ($e instanceof AppException) {
                throw $e;
            }

            throw new MenuExceptionHandler($e);
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

            error_log("updateData: " . json_encode($updateData));

            if (!$updateData) {
                throw AppException::badRequest("No se pudo actualizar el menú");
            }
            
            return new MenuEntity($updateData);
        } catch (Exception $e) {
            if ($e instanceof AppException) {
                throw $e;
            }

            throw new MenuExceptionHandler($e);
        }
    }
    

}
?>