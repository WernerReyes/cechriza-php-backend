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

    public function update(UpdateMachineDto $dto)
    {
        $machine = MachineModel::find($dto->id);
        if (!$machine) {
            throw AppException::notFound("Machine not found");
        }

        $imagePaths = $machine->imagePaths;

        if ($dto->fileImages) {
            foreach ($dto->fileImages as $image) {
                $imagePaths[] = $this->uploadFile($image);
            }
        }

        if ($dto->imagesToUpdate) {
            foreach ($dto->imagesToUpdate as $imageUpdate) {
                $oldImage = $imageUpdate['oldImage'];
                $newFile = $imageUpdate['newFile'];
                $newImagePath = $this->uploadFile($newFile);
                $key = array_search($oldImage, $imagePaths);
                if ($key !== false) {
                    $imagePaths[$key] = $newImagePath;
                }
            }
        }

        if ($dto->imagesToRemove) {
            foreach ($dto->imagesToRemove as $imageToRemove) {
                $key = array_search($imageToRemove, $imagePaths);
                if ($key !== false) {
                    unset($imagePaths[$key]);
                }
            }
            // Reindex array
            $imagePaths = array_values($imagePaths);
        }

        $manualPath = $machine->manualPath;
        if ($dto->manualFile) {
            $manualPath = $this->uploadFile($dto->manualFile, true);
        }

        $machine->update($dto->toArray($imagePaths, $manualPath));

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