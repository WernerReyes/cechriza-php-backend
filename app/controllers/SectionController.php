<?php
require_once "app/utils/FileUploader.php";
require_once "app/services/SectionService.php";
require_once "app/dtos/section/request/UpdateSectionRequestDto.php";
require_once "app/dtos/common/request/UpdateOrderRequestDto.php";
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

        $formData = $this->formData(["fileImage"]);
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
         $formData = $this->formData(["fileImage"]);

         error_log(json_encode($formData) ." formData");
        $dto = new UpdateSectionRequestDto($formData, $id);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->sectionService->update($dto), "Sección actualizada correctamente");
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
        $this->sectionService->delete(intval($id));
        return AppResponse::success(message: "Sección eliminada correctamente");
    }
}
?>