<?php
require_once "app/dtos/machine/response/MachineResponseDto.php";
class MachineService
{
    private readonly FileUploader $fileUploader;

    public function __construct()
    {
        $this->fileUploader = new FileUploader();
    }


    public function getAll()
    {
        $machines = MachineModel::orderBy('updated_at', 'desc')->get();
        return $machines->map(fn($machine) => new MachineResponseDto($machine));
    }

    public function create(CreateMachineDto $dto)
    {
        $imagePaths = [];
        foreach ($dto->fileImages as $image) {
            $imagePaths[] = $this->uploadFile($image);
        }

        $manualPath = null;
        if ($dto->manualFile) {
            $manualPath = $this->uploadFile($dto->manualFile, true);
        }

        $machine = MachineModel::create($dto->toArray($imagePaths, $manualPath));

        return new MachineResponseDto($machine);
    }


    private function uploadFile($file, $isManual = false)
    {
        $uploadResult = null;
        if ($isManual) {
            $uploadResult = $this->fileUploader->uploadFile($file);
        } else {
            $uploadResult = $this->fileUploader->uploadImage($file);
        }
        if (
            isset($uploadResult["error"]) &&
            $uploadResult["error"]
        ) {
            throw AppException::badRequest("File upload failed: " . $uploadResult["error"]);
        }
        return $uploadResult['path'];
    }
}