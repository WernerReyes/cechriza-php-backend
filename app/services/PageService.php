<?php
require_once "app/exceptions/PageExceptionHandler.php";
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

            throw new PageExceptionHandler($e);
        }
    }
}

?>