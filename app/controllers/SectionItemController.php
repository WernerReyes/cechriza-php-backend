<?php
require_once "app/services/SectionItemService.php";
require_once "app/dtos/sectionItem/request/CreateSectionItemRequestDto.php";
class SectionItemController extends AppController
{
    private SectionItemService $sectionItemService;


    public function __construct()
    {
        $this->sectionItemService = new SectionItemService();
    }

    public function create()
    {
        $body = $this->body();
        $dto = new CreateSectionItemRequestDto($body);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->sectionItemService->create($dto));
    }
}