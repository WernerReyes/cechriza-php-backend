<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class OS_TICKET
{


    private function DBConnection()
    {
        $serverName = "172.19.0.14"; // or your server name
        $port = "3306";
        $database = "osticket";
        $username = "devuser";
        $password = "password";
        try {
            // ✅ CONVERTIDO: DSN para MySQL
            $dsn = "mysql:host=$serverName;port=$port;dbname=$database;charset=utf8mb4";
            $conn = new PDO($dsn, $username, $password);

            // ✅ Opciones recomendadas para MySQL
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            return $conn;

        } catch (PDOException $e) {
            error_log("MySQL Connection failed: " . $e->getMessage());
            die("Connection failed: " . $e->getMessage());
        }
    }

    private function readExcelFile($conn)
    {
        $inputFileName = 'C:\Users\Cechriza\Downloads\AGENCIAS - david 2 (1).xlsx';
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

        // return $data;


        $filter = array_filter($data, fn($row, $i) => $i > 1, ARRAY_FILTER_USE_BOTH);
        return array_values($filter);

    }


    private function getDepartments($conn, $excelData)
    {
        foreach ($excelData as $index => $row) {
            $deptName = $row[7]; // Columna H (índice 7)

            $stmt = $conn->prepare("SELECT id_departamento FROM departamento WHERE departamento = ?");
            $stmt->execute([$deptName]);
            $dept = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dept) {
                // echo "Departamento encontrado: " . json_encode($dept) . "\n";
                $excelData[$index][7] = $dept['id_departamento']; // Reemplaza nombre por ID
            } else {
                // echo "Departamento no encontrado para: $deptName\n";
                // Puedes mantener el nombre o asignar null si prefieres
                $excelData[$index][7] = null;
            }
        }

        return $excelData; // Devuelve el array modificado
    }

    private function getProvinces($conn, $excelData)
    {
        foreach ($excelData as $index => $row) {
            $provinceName = $row[8]; // Columna J (índice 9)

            $stmt = $conn->prepare("SELECT id_provincia FROM provincia WHERE provincia = ?");
            $stmt->execute([$provinceName]);
            $province = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($province) {
                // echo "Provincia encontrada: " . json_encode($province) . "\n";
                $excelData[$index][8] = $province['id_provincia']; // Reemplaza nombre por ID
            } else {
                // echo "Provincia no encontrada para: $provinceName\n";
                // Puedes mantener el nombre o asignar null si prefieres
                $excelData[$index][8] = null;
            }
        }

        return $excelData; // Devuelve el array modificado
    }

    private function getDistricts($conn, $excelData)
    {
        foreach ($excelData as $index => $row) {
            $districtName = $row[9]; // Columna I (índice 9)

            $stmt = $conn->prepare("SELECT id_distrito FROM distrito WHERE distrito = ?");
            $stmt->execute([$districtName]);
            $district = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($district) {
                // echo "Distrito encontrado: " . json_encode($district) . "\n";
                $excelData[$index][9] = $district['id_distrito']; // Reemplaza nombre por ID
            } else {
                // echo "Distrito no encontrado para: $districtName\n";
                // Puedes mantener el nombre o asignar null si prefieres
                $excelData[$index][9] = null;
            }
        }

        return $excelData; // Devuelve el array modificado
    }

    private function getCompanies($conn, $excelData)
    {
        foreach ($excelData as $index => $row) {
            $companyName = $row[10]; // Columna K (índice 10)

            $stmt = $conn->prepare("SELECT id FROM ost_list_items WHERE value = ?");
            $stmt->execute([$companyName]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($company) {
                // echo "Empresa encontrada: " . json_encode($company) . "\n";
                $excelData[$index][10] = $company['id']; // Reemplaza nombre por ID
            } else {
                // echo "Empresa no encontrada para: $companyName\n";
                // Puedes mantener el nombre o asignar null si prefieres
                $excelData[$index][10] = null;
            }
        }

        return $excelData; // Devuelve el array modificado
    }


    private function getCDepartment($conn, $excelData)
    {
        foreach ($excelData as $index => $row) {
            $cDeptName = $row[2]; // Columna C (índice 2)

            $stmt = $conn->prepare("SELECT id FROM ost_department WHERE name = ?");
            $stmt->execute([$cDeptName]);
            $cDept = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cDept) {
                // echo "CDepartamento encontrado: " . json_encode($cDept) . "\n";
                $excelData[$index][2] = $cDept['id']; // Reemplaza nombre por ID
            } else {
                // echo "CDepartamento no encontrado para: $cDeptName\n";
                // Puedes mantener el nombre o asignar null si prefieres
                $excelData[$index][2] = null;
            }
        }

        return $excelData; // Devuelve el array modificado
    }


    private function getArea($conn, $excelData)
    {
        foreach ($excelData as $index => $row) {
            $areaName = $row[3]; // Columna D (índice 3)

            $stmt = $conn->prepare("SELECT id_area FROM area WHERE descripcion_area = ?");
            $stmt->execute([$areaName]);
            $area = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($area) {
                // echo "Área encontrada: " . json_encode($area) . "\n";
                $excelData[$index][3] = $area['id_area']; // Reemplaza nombre por ID
            } else {
                // echo "Área no encontrada para: $areaName\n";
                // Puedes mantener el nombre o asignar null si prefieres
                $excelData[$index][3] = null;
            }
        }

        return $excelData; // Devuelve el array modificado
    }

    private function getCargo($conn, $excelData)
    {
        $CESADO_AREA_ID = 5; // ID del área "CESADO" (ajusta según tu base de datos)
        foreach ($excelData as $index => $row) {
            $cargoName = $row[4]; // Columna E (índice 4)

            $stmt = $conn->prepare("SELECT id_cargo, id_area FROM cargo WHERE descripcion_cargo = ?");
            $stmt->execute([$cargoName]);
            $cargo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cargo) {
                echo $cargo['id_area'];
                // echo "Cargo encontrado: " . json_encode($cargo) . "\n";
                if ($excelData[$index][3] == $CESADO_AREA_ID) {
                    // Si el área es "CESADO", asigna un cargo específico o null
                    $excelData[$index][3] = $cargo['id_area'];

                }
                $excelData[$index][4] = $cargo['id_cargo']; // Reemplaza nombre por ID
            } else {
                // echo "Cargo no encontrado para: $cargoName\n";
                // Puedes mantener el nombre o asignar null si prefieres
                $excelData[$index][4] = null;
            }
        }

        return $excelData; // Devuelve el array modificado
    }





    private function excelParsed($excelData)
    {
        return array_map(function ($row) {
            return [
                'id' => $row[0],
                'id_cliente' => $row[3],
                // 'Cargo' => $row[4],
                // 'CDepartamento' => $row[2],
                // 'Nombre' => $row[3],
                // 'DNI' => $row[4],
                // 'Usuario' => $row[5],
                // 'Email' => $row[6],
                // 'Departamento' => $row[7],
                // 'Provincia' => $row[8], // Ya convertido a ID
                // 'Distrito' => $row[9],   // Ya convertido a ID
                // 'Empresa' => $row[10],    // Ya convertido a ID
                // 'Fecha_ingreso' => $this->parseDate($row[11]),
                // 'N_contacto' => $row[12],
            ];
        }, $excelData);
    }

    private function parseDate($date)
    {
        if ($date === null || $date === '' || $date === '#N/A' || !is_numeric($date)) {
            return null; // o return 'N/A';
        }

        // Excel cuenta desde 1900-01-01, pero PHP usa 1970-01-01
        // Por eso restamos 25569 (días entre 1900 y 1970)
        $timestamp = ($date - 25569) * 86400;

        return gmdate("Y-m-d", $timestamp);
    }

    private function updateDB($conn, $dataToUpdate)
    {
        $limit = 2; // Límite de registros a procesar
        $counter = 0;
        $totalUpdated = 0;
        $totalNotUpdated = 0;

        

        foreach ($dataToUpdate as $row) {
            // if (++$counter > $limit) break;

            $stmt = $conn->prepare("UPDATE ost_list_items SET
            id_cliente = ?
            WHERE id = ?");

            $stmt->execute([
                $row['id_cliente'],
                $row['id']
            ]);

            if ($stmt->rowCount() > 0) {
                $totalUpdated++;
                echo "✅ Registro actualizado correctamente (staff_id = {$row['id']})\n";
            } else {
                $totalNotUpdated++;
                echo "⚠️ No se actualizó el registro (staff_id = {$row['id']}) — posiblemente no existe o no cambió.\n";
            }
        }

        echo json_encode([
            'summary' => [
                'total_processed' => $counter <= $limit ? $counter : $limit,
                'total_updated' => $totalUpdated,
                'total_not_updated' => $totalNotUpdated
            ]
        ], JSON_PRETTY_PRINT) . "\n";
    }




    public function runScript()
    {
        echo "Iniciando script...\n";
        $conn = $this->DBConnection();


        $exelData = $this->readExcelFile($conn);

        $dataToUpdate = $exelData;
        // $dataToUpdate = $this->getCDepartment($conn, $dataToUpdate);
        // $dataToUpdate = $this->getDepartments($conn, $dataToUpdate);
        // $dataToUpdate = $this->getProvinces($conn, $dataToUpdate);
        // $dataToUpdate = $this->getDistricts($conn, $dataToUpdate);
        // $dataToUpdate = $this->getCompanies($conn, $dataToUpdate);
        // $dataToUpdate = $this->getArea($conn, $dataToUpdate);
        // $dataToUpdate = $this->getCargo($conn, $dataToUpdate);
        $dataToUpdate = $this->excelParsed($dataToUpdate);
        $this->updateDB($conn, $dataToUpdate);

        // echo "Datos leídos del archivo Excel: " . count($dataToUpdate) . " filas.\n";

        echo "<h3>Datos del Excel (" . json_encode($dataToUpdate) . " total):</h3>";





        try {
            $stmt = $conn->query("SELECT * FROM ost_staff");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($data)) {
                echo "<p>No se encontraron registros en ost_staff</p>";
                return;
            }

            echo "<h3>Registros de ost_staff (" . count($data) . " total):</h3>";
            echo "<table border='1' cellpadding='5' cellspacing='0'>";

            // ✅ Headers de la tabla
            echo "<tr style='background-color: #f0f0f0;'>";
            foreach (array_keys($data[0]) as $column) {
                echo "<th>" . htmlspecialchars($column) . "</th>";
            }
            echo "</tr>";

            // ✅ Datos de la tabla
            foreach ($data as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }

            echo "</table>";

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }

    }





}

?>