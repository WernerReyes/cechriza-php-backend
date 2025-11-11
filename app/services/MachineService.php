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


        // 5️⃣ Ahora que ya existe, encolamos las optimizaciones
        $images = json_decode($machine->images, true);
        foreach ($images as $path) {
            $fullPath = $this->fileUploader->getFullPathFromUrl($path['url']);
            $this->enqueueOptimization($fullPath, $machine->id_machine);
        }

        $machine->load('category:id_category,type');

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

        $imagePathsToOptimize = [];


        if ($dto->fileImages) {
            foreach ($dto->fileImages as $image) {
                $newImagePath = [
                    'isMain' => false,
                    'url' => $this->uploadFile($image)
                ];
                $imagePaths[] = $newImagePath;
                $imagePathsToOptimize[] = $this->fileUploader->getFullPathFromUrl(
                    $newImagePath['url']
                );
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

                $imagePathsToOptimize[] = $this->fileUploader->getFullPathFromUrl($newImagePath);

                $this->fileUploader->deleteImage($path);
            }
        }

        if ($dto->imagesToRemove) {
            foreach ($dto->imagesToRemove as $imageToRemove) {
                $path = $this->normalizePath($this->fileUploader->getPathFromUrl($imageToRemove));
                $key = array_search($path, array_column($imagePaths, 'url'));
                error_log("Removing image at path: " . $path . " found at key: " . $key);
                if ($key !== false) {
                    unset($imagePaths[$key]);
                }

               

                $deleted = $this->fileUploader->deleteImage($path);
              
            }
            // Reindex array
            $imagePaths = array_values(array: $imagePaths);

           
        }

        $manualPath = $machine->manual;
        if ($dto->manualFile) {
            if ($manualPath) {
                $this->fileUploader->deleteFile($manualPath);
            }

            $manualPath = $this->uploadFile($dto->manualFile, true);
        }

        $machine->update($dto->toArray($imagePaths, $manualPath));

        
        // 5️⃣ Ahora que ya existe, encolamos las optimizaciones
        if ($imagePathsToOptimize) {
            foreach ($imagePathsToOptimize as $path) {
                $this->enqueueOptimization($path, $machine->id_machine);
            }
        }
        
        $machine->load('sections:id_section');
        $machine->load('category:id_category,type');

        return new MachineResponseDto($machine);
    }


    /**
     * Encola un job JSON para que el worker optimice la imagen.
     */
    private function enqueueOptimization(string $path, $machineId): void
    {
        $queueDir = __DIR__ . '/../queue/jobs/';
        if (!is_dir($queueDir)) {
            mkdir($queueDir, 0755, true);
        }

        $jobId = uniqid('job_', true);
        $job = [
            'type' => 'optimize_image',
            'machine_id' => $machineId,
            'path' => $path,
            'created_at' => date('Y-m-d H:i:s')
        ];

        file_put_contents($queueDir . "$jobId.json", json_encode($job, JSON_PRETTY_PRINT));

        // Inicia el worker en background si no está corriendo
        $this->startWorkerIfNotRunning();
    }

    /**
     * Inicia el worker solo si no está activo.
     */
    private function startWorkerIfNotRunning(): void
{
    $workerPath = __DIR__ . '/../queue/worker.php';
    $escapedWorkerPath = escapeshellarg($workerPath);

    if (stristr(PHP_OS, 'WIN')) {
        // Buscar si el worker ya está corriendo
        $output = [];
        exec('tasklist /FI "IMAGENAME eq php.exe"', $output);

        $isRunning = false;
        foreach ($output as $line) {
            if (str_contains($line, 'php.exe')) {
                // Verificamos si el comando worker.php está en uso
                $cmdCheck = shell_exec('wmic process where "CommandLine like \'%worker.php%\'" get CommandLine 2>nul');
                if (str_contains($cmdCheck ?? '', 'worker.php')) {
                    $isRunning = true;
                    break;
                }
            }
        }

        if (!$isRunning) {
            // Iniciar en segundo plano
            pclose(popen("start /B php $escapedWorkerPath", "r"));
        }
    } else {
        // Linux / Mac
        $isRunning = shell_exec("pgrep -f 'php .*worker.php'");
        if (!$isRunning) {
            exec("nohup php $escapedWorkerPath > /dev/null 2>&1 &");
        }
    }
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