<?php
require_once "app/services/PageService.php";
require_once "app/dtos/page/request/CreatePageRequestDto.php";
class PageController extends AppController
{
    private PageService $pageService;

    public function __construct()
    {
        $this->pageService = new PageService();
    }

    public function getAll()
    {
        return AppResponse::success($this->pageService->getAll());
    }

    public function create()
    {
        $body = $this->body();
        $dto = new CreatePageRequestDto($body);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->pageService->create($dto));
    }
}
?>