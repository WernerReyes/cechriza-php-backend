<?php
require_once "app/data/Seed.php";
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class SeedController
{

    private $seed;
    public function __construct()
    {
        $this->seed = new Seed();
    }
    public function run()
    {
        $this->seed->run();
    }

    private function sqlServerDBConnection()
    {
        $serverName = "172.19.0.18"; // or your server name
        $database = "CECHRIZA-PRODUCCION";
        $username = "sa";
        $password = "Angelicus";
        try {
            $conn = new PDO("sqlsrv:Server=$serverName;Database=$database;TrustServerCertificate=true", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    private function readExcelFile($conn)
    {
        $inputFileName = 'C:\Users\Cechriza\Downloads\consulta_resultado(Series).xlsx';
        $spreadsheet = IOFactory::load($inputFileName);
        $sheet = $spreadsheet->getActiveSheet();

        $data = [];


        foreach ($sheet->getRowIterator() as $row) {
            // if (++$currentRow > $maxRows)
            //     break;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }

            $data[] = $rowData;
            
        }

        
        return array_filter($data, fn($row, $i) => $i > 0);

    }
  
public function runScript() {
    $conn = $this->sqlServerDBConnection();

    //* First from 0 to 4000 "summary": {
    //     "total_updated": 3409,
    //     "total_not_found": 593
    // }
   
    $maxRows = 9000;
    $startRow = 4001;
    // $currentRow = 4001;
    $excelData = DataSeeder::getExelData();

    $totalUpdated = 0;
    $totalNotFound = 0;

     // ✅ CORRECTO - Usar array_slice para empezar desde la fila 2001
    $slicedData = array_slice($excelData, $startRow, $maxRows - $startRow + 1);

    echo json_encode([
        'info' => "Procesando filas desde $startRow hasta $maxRows",
        'total_rows_to_process' => $slicedData[0]
    ]) . "\n";
    
    // $currentRow = $startRow - 1; // Empezar desde 2000 para que el primer ++$currentRow sea 2001

    // ✅ ALTERNATIVA - Usar índice del foreach
    foreach ($excelData as $index => $row) {
        // $currentRow = $index + 1; // Los arrays empiezan en 0, pero las filas en 1
        
        // // Saltar filas hasta llegar a la 2001
        // if ($currentRow < $startRow) {
        //     continue;
        // }
        
        // // Parar al llegar al máximo
        // if ($currentRow > $maxRows) {
        //     break;
        // }
            
        $serie = $row[3];
        $idMachine = $row[0];
        $idAgent = $row[1];
        
        // 1. Primero verificar si el registro existe
        $checkStmt = $conn->prepare("SELECT internalSN FROM OINS WHERE internalSN = ?");
        $checkStmt->execute([$serie]);
        $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($exists) {
            // 2. Si existe, hacer el UPDATE
            $updateStmt = $conn->prepare("UPDATE OINS SET U_id_equipo_s = ?, U_id_cliente_s = ? WHERE internalSN = ?");
            $updateStmt->execute([$idMachine, $idAgent, $serie]);
            
            $affectedRows = $updateStmt->rowCount();
            
            echo json_encode([
                'serie' => $serie,
                'action' => 'updated',
                'success' => $affectedRows > 0,
                'affected_rows' => $affectedRows
            ]) . "\n";

            $totalUpdated += $affectedRows;
            
        } else {
            echo json_encode([
                'serie' => $serie,
                'action' => 'not_found',
                'message' => "Serie no encontrada en la base de datos"
            ]) . "\n";

            $totalNotFound += $affectedRows;
        }
    }

    echo json_encode([
        'summary' => [
            'total_updated' => $totalUpdated,
            'total_not_found' => $totalNotFound
        ]
    ]) . "\n";
}




}

?>

