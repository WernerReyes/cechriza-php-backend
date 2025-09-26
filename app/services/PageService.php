<?php
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/models/PageModel.php";
require_once "app/models/MenuModel.php";
require_once "app/entities/PageEntity.php";
class PageService
{
    private PageModel $pageModel;
    private MenuModel $menuModel;
    public function __construct()
    {
        $this->pageModel = PageModel::getInstance();
        $this->menuModel = MenuModel::getInstance();
    }

    public function getAll()
    {
        $pages = $this->pageModel->getAll();
        return array_map(fn($page) => new PageEntity($page), $pages);
    }

    public function create(CreatePageRequestDto $dto): PageEntity
    {
        try {
            //code...
            $menu = $this->menuModel->getByField(MenuSearchField::ID, $dto->menuId);
            if (empty($menu)) {
                throw AppException::badRequest("El menu no existe");
            }

            $pageCreated = $this->pageModel->create($dto->toInsertDB());
            return new PageEntity($pageCreated);
        } catch (Exception $e) {
            if ($e instanceof AppException) {
                throw $e;
            }

            throw new DBExceptionHandler($e, [
                ["name" => "unique_menu_id", "message" => "No se puede asignar el mismo menú a dos páginas."],
            ]);
        }
    }
}
