<?php
require_once "app/services/SectionItemService.php";
require_once "app/dtos/sectionItem/request/CreateSectionItemRequestDto.php";
require_once "app/dtos/sectionItem/request/UpdateSectionItemRequestDto.php";
class SectionItemController extends AppController
{
    private readonly SectionItemService $sectionItemService;


    public function __construct()
    {
        $this->sectionItemService = new SectionItemService();
    }

    public function create()
    {
        $formData = $this->formData(["fileImage", "backgroundFileImage", "fileIcon"]);
        $dto = new CreateSectionItemRequestDto($formData);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->sectionItemService->create($dto), "Item de sección creado correctamente");
    }

    public function update($id)
    {
        $formData = $this->formData(["fileImage", "backgroundFileImage", "fileIcon"]);
        $dto = new UpdateSectionItemRequestDto($formData, $id);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }
        
        return AppResponse::success($this->sectionItemService->update($dto), "Item de sección actualizado correctamente");
    }

    public function delete($id)
    {
        return AppResponse::success($this->sectionItemService->delete(intval($id)), "Item de sección eliminado correctamente");
    }
}