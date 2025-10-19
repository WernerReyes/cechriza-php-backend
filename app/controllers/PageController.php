<?php
require_once "app/services/PageService.php";
require_once "app/dtos/page/request/GetAllPagesFilterRequestDto.php";
require_once "app/dtos/page/request/CreatePageRequestDto.php";
require_once "app/dtos/page/request/UpdatePageRequestDto.php";
class PageController extends AppController
{
    private PageService $pageService;

    public function __construct()
    {
        $this->pageService = new PageService();
    }

    public function getAll()
    {
        $query = $_GET;
        $dto = new GetAllPagesFilterRequestDto($query);
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }
        return AppResponse::success($this->pageService->getAll($dto));
    }

    public function getById($id)
    {
        $page = $this->pageService->getById(intval($id));
        return AppResponse::success($page);
    }

    public function create()
    {
        $body = $this->body();
        $dto = new CreatePageRequestDto($body);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->pageService->create($dto), "Página creada exitosamente");
    }

    public function update($id)
    {
        $body = $this->body();
        $dto = new UpdatePageRequestDto($body, $id);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->pageService->update($dto), "Página actualizada exitosamente");
    }
    

    public function delete($id)
    {
        $this->pageService->delete(intval($id));
        return AppResponse::success(message: "Página eliminada exitosamente");
    }
}
?>