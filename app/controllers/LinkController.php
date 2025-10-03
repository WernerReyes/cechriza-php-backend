<?php
require_once "app/AppController.php";
require_once "app/AppResponse.php";
require_once "app/services/LinkService.php";
require_once "app/dtos/link/request/CreateLinkRequestDto.php";

class LinkController extends AppController
{

    private LinkService $linkService;
    public function __construct()
    {
        $this->linkService = new LinkService();
    }

    public function getAll()
    {
        $links = $this->linkService->getAll();
        return AppResponse::success($links);
    }

    public function create()
    {
        $body = $this->body();
        $dto = new CreateLinkRequestDto($body);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->linkService->create($dto), "Link creado exitosamente");
    }
}
