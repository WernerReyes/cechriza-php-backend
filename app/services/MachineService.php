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
            $uploadResult = $this->fileUploader->uploadImage($image, 'machines');
            error_log("Upload result: " . json_encode($uploadResult));
            if (
                isset($uploadResult["error"]) &&
                $uploadResult["error"]
            ) {
                throw AppException::badRequest("Image upload failed: " . $uploadResult["error"]);
            }
            $imagePaths[] = $uploadResult['path'];
        }

        $machine = MachineModel::create($dto->toArray($imagePaths));

        return new MachineResponseDto($machine);
    }
}