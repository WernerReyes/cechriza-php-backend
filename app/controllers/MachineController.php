<?php
require_once "app/services/MachineService.php";
require_once "app/dtos/machine/request/CreateMachineDto.php";
require_once "app/dtos/machine/request/UpdateMachineDto.php";
class MachineController extends AppController
{

    private readonly MachineService $machineService;

    public function __construct()
    {
        parent::__construct();
        $this->machineService = new MachineService();
    }

    public function getAll()
    {
        return AppResponse::success($this->machineService->getAll());
    }

    public function create()
    {
        $formData = $this->formData(["fileImages", "manualFile"]);
        error_log(json_encode($formData));
        $dto = new CreateMachineDto($formData);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success(
            $this->machineService->create($dto),
            "Máquina creada exitosamente"
        );

    }


    public function update($id)
    {
        $formData = $this->formData(["fileImages", "manualFile", "imagesToUpdateNew"]);


        error_log(json_encode($formData));

        $dto = new UpdateMachineDto($formData, $id);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success(
            $this->machineService->update($dto),
            "Máquina actualizada exitosamente"
        );
    }

    public function setImageAsMain($id)
    {
        $imageUrl = $this->body('imageUrl');
        if (empty($imageUrl)) {
            throw AppException::badRequest("La ruta de la imagen es obligatoria");
        }

        return AppResponse::success(
            $this->machineService->setImageasMain($id, $imageUrl),
            "Imagen principal establecida exitosamente"
        );
    }

    public function delete($id)
    {
        return AppResponse::success(
            $this->machineService->delete($id),
            "Máquina eliminada exitosamente"
        );
    }

}