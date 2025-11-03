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
        $machines = MachineModel::with(
            'category:id_category,title,type',
            'link:id_link,url,title,file_path,type'
        )->orderBy('updated_at', 'desc')->get();
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

        if ($dto->linkId) {
            $link = LinkModel::find($dto->linkId);
            if (empty($link)) {
                throw AppException::badRequest("El enlace seleccionado no existe");
            }
        }

        $machine = MachineModel::create($dto->toArray($imagePaths, $manualPath));

        return new MachineResponseDto($machine);
    }

    public function update(UpdateMachineDto $dto)
    {
        error_log("Updating machine with DTO: " . json_encode($dto));

        $machine = MachineModel::find($dto->id);
        if (!$machine) {
            throw AppException::notFound("Machine not found");
        }

        if ($dto->linkId) {
            $link = LinkModel::find($dto->linkId);
            if (empty($link)) {
                throw AppException::badRequest("El enlace seleccionado no existe");
            }
        }

        $imagePaths = json_decode($machine->images ?? [], true);

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
                $path = $this->fileUploader->getPathFromUrl($oldImage);
                $key = array_search($path, $imagePaths);
                if ($key !== false) {
                    $imagePaths[$key] = $newImagePath;
                }
            }
        }

        if ($dto->imagesToRemove) {
            foreach ($dto->imagesToRemove as $imageToRemove) {
                $path = $this->fileUploader->getPathFromUrl($imageToRemove);
                $key = array_search($path, $imagePaths);
                if ($key !== false) {
                    unset($imagePaths[$key]);
                }

                // if ($imageToRemove->newFile) {
                //     $newImagePath = $this->uploadFile($imageToRemove->newFile);
                //     $imagePaths[$key] = $newImagePath;
                // }
            }
            // Reindex array
            $imagePaths = array_values($imagePaths);
        }

        $manualPath = $machine->manualPath;
        if ($dto->manualFile) {
            $manualPath = $this->uploadFile($dto->manualFile, true);
        }

        $machine->update($dto->toArray($imagePaths, $manualPath));

        $machine->load('sections:id_section');

        return new MachineResponseDto($machine);
    }


    public function delete(int $id)
    {

        try {
            $machine = MachineModel::find($id);
            if (!$machine) {
                throw AppException::notFound("Machine not found");
            }


            $machine->delete();


            if ($machine->images) {
                $images = json_decode($machine->images, true);
                foreach ($images as $imagePath) {
                    $this->fileUploader->deleteFile($imagePath);
                }
            }

            if ($machine->manualPath) {
                $this->fileUploader->deleteFile($machine->manualPath);
            }
        } catch (Exception $e) {
            if (get_class($e) === "AppException") {
                throw $e;
            }
            throw new DBExceptionHandler($e, [
                ["name" => "fk_section_machines_machine", "message" => "No se puede eliminar la máquina porque está asociada a una o más secciones"]
            ]);
        }


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