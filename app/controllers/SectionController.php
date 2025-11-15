<?php
require_once "app/utils/FileUploader.php";
require_once "app/services/SectionService.php";
require_once "app/dtos/section/request/UpdateSectionRequestDto.php";
require_once "app/dtos/common/request/UpdateOrderRequestDto.php";
require_once "app/dtos/section/request/AssocieteToPagesRequestDto.php";
class SectionController extends AppController
{
    private SectionService $sectionService;

    public function __construct()
    {
        $this->sectionService = new SectionService();
    }


    public function getAll()
    {
        return AppResponse::success($this->sectionService->getAll(), "Secciones obtenidas correctamente");
    }

    public function create()
    {

        $formData = $this->formData(["fileImage", "fileIcon", 'fileVideo']);
        // $body = $this->body();
        error_log(json_encode($formData) ." formData");
        $dto = new CreateSectionRequestDto($formData);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->sectionService->create($dto), "Sección creada correctamente");
    }

    public function update($id)
    {
         $formData = $this->formData(["fileImage", "fileIcon", 'fileVideo']);

         error_log(json_encode($formData) ." formData");
        $dto = new UpdateSectionRequestDto($formData, $id);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->sectionService->update($dto), "Sección actualizada correctamente");
    }

    public function duplicate($id)
    {
        $pageId = $this->body()["pageId"] ?? null;
        if (empty($pageId)) {
            throw AppException::badRequest("El parámetro pageId es obligatorio");
        }
        return AppResponse::success($this->sectionService->duplicate(intval($id), intval($pageId)), "Sección duplicada correctamente");
    }

    public function associeteToPages($id)
    {
        $body = $this->body();
        $dto = new AssocieteToPagesRequestDto($body, $id);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->sectionService->associeteToPages($dto), "Sección asociada a páginas correctamente");
    }


    public function moveToPage($id)
    {
        $fromPageId = $this->body()["fromPageId"] ?? null;
        $toPageId = $this->body()["toPageId"] ?? null;
        if (empty($fromPageId) || empty($toPageId)) {
            throw AppException::badRequest("Los parámetros fromPageId y toPageId son obligatorios");
        }

       
        return AppResponse::success($this->sectionService->moveToPage(intval($id), intval($fromPageId), intval($toPageId)), "Sección movida correctamente a la página destino");
    }

    public function updateOrder()
    {
        $body = $this->body();
        $dto = new UpdateOrderRequestDto($body);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        $this->sectionService->updateOrder($dto);
        return AppResponse::success(null, "Orden de secciones actualizado correctamente");
    }


    public function delete($id)
    {
        $pageId = $this->queryParam("pageId");
        $this->sectionService->delete(intval($id), $pageId);
        return AppResponse::success(message: "Sección eliminada correctamente");
    }
}
?>