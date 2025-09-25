<?php
require_once "app/services/SectionService.php";
class SectionController extends AppController
{
    private SectionService $sectionService;

    public function __construct()
    {
        $this->sectionService = new SectionService();
    }

    public function create()
    {
        $body = $this->body();
        $dto = new CreateSectionRequestDto($body);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->sectionService->create($dto));
    }
}
?>