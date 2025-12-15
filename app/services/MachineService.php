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




        $machine->load('category:id_category,type,title');

        if ($machine->link_id) {
            $machine->load('link:id_link,type,title,url,file_path,page_id');
        }

        return new MachineResponseDto($machine);
    }

    public function update(UpdateMachineDto $dto)
    {

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
                $newImagePath = [
                    'isMain' => false,
                    'url' => $this->uploadFile($image)
                ];
                $imagePaths[] = $newImagePath;

            }
        }

        if ($dto->imagesToUpdate) {
            foreach ($dto->imagesToUpdate as $imageUpdate) {
                $oldImage = $imageUpdate['oldImage'];
                $newFile = $imageUpdate['newFile'];
                $newImagePath = $this->uploadFile($newFile);
                $path = $this->normalizePath($this->fileUploader->getPathFromUrl($oldImage));
                $key = array_search($path, array_column($imagePaths, 'url'));
                if ($key !== false) {
                    $imagePaths[$key] = [
                        'isMain' => $imagePaths[$key]['isMain'],
                        'url' => $newImagePath
                    ];
                }

                $this->fileUploader->deleteImage($path);
            }
        }

        if ($dto->imagesToRemove) {
            foreach ($dto->imagesToRemove as $imageToRemove) {
                $path = $this->normalizePath($this->fileUploader->getPathFromUrl($imageToRemove));

                $key = array_search($path, array_column($imagePaths, 'url'));
                if ($key !== false) {
                    unset($imagePaths[$key]);
                }



                $this->fileUploader->deleteImage($path);

            }
            // Reindex array
            $imagePaths = array_values($imagePaths);


        }

        $manualPath = $machine->manual;
        if ($dto->manualFile) {
            if ($manualPath) {
                $this->fileUploader->deleteFile($manualPath);
            }

            $manualPath = $this->uploadFile($dto->manualFile, true);
        }

        $machine->update($dto->toArray($imagePaths, $manualPath));



        $machine->load('sections:id_section');
        $machine->load('category:id_category,type,title');

        if ($machine->link_id) {
            $machine->load('link:id_link,type,title,url,file_path,page_id');
        }

        return new MachineResponseDto($machine);
    }


    public function updateTechnicalSpecifications(int $id, array $technicalSpecifications)
    {
        $machine = MachineModel::find($id);
        if (!$machine) {
            throw AppException::notFound("Machine not found");
        }

        $machine->update([
            'technical_specifications' => json_encode(array_map(function ($spec) {
                return [
                    'id' => UuidUtil::v4(),
                    'title' => $spec['title'],
                    'description' => $spec['description']
                ];
            }, $technicalSpecifications))
        ]);

        return new MachineResponseDto($machine);
    }





    private function normalizePath($p)
    {
        return str_replace('\\', '/', $p);
    }

    public function setImageAsMain(int $id, $imageUrl)
    {
        $machine = MachineModel::find($id);
        if (!$machine) {
            throw AppException::notFound("Machine not found");
        }

        $path = $this->fileUploader->getPathFromUrl($imageUrl);

        $images = array_map(function ($img) use ($path) {
            if ($img['url'] === $path) {
                return [
                    'isMain' => true,
                    'url' => $img['url']
                ];
            }
            return [
                'isMain' => false,
                'url' => $img['url']
            ];

        }, json_decode($machine->images, true));


        $machine->update(['images' => json_encode($images)]);

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
                    $this->fileUploader->deleteImage($imagePath['url']);
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
            $uploadResult = $this->fileUploader->uploadImage($file, true);
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