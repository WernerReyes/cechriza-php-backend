<?php
require_once "app/services/MachineService.php";
require_once "app/dtos/machine/request/CreateMachineDto.php";
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
        $formData = $this->formData(["fileImages"]);
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

}