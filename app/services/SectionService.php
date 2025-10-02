<?php
require_once "app/models/SectionModel.php";
require_once "app/models/PageModel.php";
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/dtos/section/request/CreateSectionRequestDto.php";
class SectionService
{

    private SectionModel $sectionModel;
    private PageModel $pageModel;

    public function __construct()
    {
        $this->sectionModel = SectionModel::getInstance();
        $this->pageModel = PageModel::getInstance();
    }

    public function create(CreateSectionRequestDto $dto)
    {
        try {
            $page = $this->pageModel->getByField(PageSearchField::ID, $dto->pageId);
            if (empty($page)) {
                throw AppException::badRequest("La página no existe");
            }
            return $this->sectionModel->create($dto->toInsertDB());

        } catch (Exception $e) {
            if ($e instanceof AppException) {
                throw $e;
            }

            throw new DBExceptionHandler($e, [
                ["name" => "order_pages_UNIQUE", "message" => "No se puede asignar el mismo orden a dos secciones en la misma página."],
            ]);
        }
    }
}