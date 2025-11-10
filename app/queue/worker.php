<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once "app/models/MachineModel.php";
require_once "config/Database.php";

//* Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();


use WebPConvert\WebPConvert;

Database::connect();

$jobsPath = __DIR__ . '/jobs/';
$logPath = __DIR__ . '/worker.log';

while (true) {
    $files = glob($jobsPath . '*.json');

    foreach ($files as $file) {
        $job = json_decode(file_get_contents($file), true);
        if (!$job) {
            unlink($file);
            continue;
        }

        if ($job['type'] === 'optimize_image') {
            $path = normalizePath($job['path']);
            $webpPath = preg_replace('/\.[a-zA-Z]+$/', '.webp', $path);

            if (file_exists($path)) {
                try {
                    // Convierte a .webp
                    WebPConvert::convert($path, $webpPath, [
                        'quality' => 85,
                        'method' => 6,
                        'max-quality' => 90,
                    ]);

                    // Borra el original y conserva solo el .webp
                    unlink($path);

                    // 🔄 Actualiza la ruta en la base de datos
                    updateMachineImage($job['machine_id'], $path, $webpPath);

                    file_put_contents($logPath, "[" . date('Y-m-d H:i:s') . "] Optimized: $webpPath\n", FILE_APPEND);
                } catch (Exception $e) {
                    file_put_contents($logPath, "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            } else {
                file_put_contents($logPath, "[" . date('Y-m-d H:i:s') . "] File not found: $path\n", FILE_APPEND);
            }

            unlink($file);
        }
    }

    sleep(2);
}


// 🔧 Normaliza ruta
function normalizePath($path)
{
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    return realpath($path) ?: $path;
}


// 🧠 Actualiza columna JSON `images` en la BD
function updateMachineImage($machineId, $oldPath, $newPath)
{
    $machine = MachineModel::find($machineId);
    if (!$machine) {
        error_log("❌ Machine not found: $machineId");
        return;
    }

    $images = json_decode($machine->images, true);

    if (!is_array($images)) {
        error_log("⚠️ Images field is not a valid JSON array for machine ID $machineId");
        return;
    }

    $updated = false;
    $oldFile = basename($oldPath);
    $newFile = basename($newPath);

    foreach ($images as &$img) {
        // Asegurar que tenga la clave 'url'
        if (isset($img['url']) && str_contains($img['url'], $oldFile)) {
            $img['url'] = str_replace($oldFile, $newFile, $img['url']);
            $updated = true;
        }
    }

    if ($updated) {
        $machine->images = json_encode($images);
        $machine->save();
        error_log("✅ Updated image path in machine ID $machineId from $oldPath to $newPath");
    } else {
        error_log("ℹ️ No image matched for machine ID $machineId");
    }
}
