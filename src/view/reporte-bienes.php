<?php

require './vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Verificar si las constantes están definidas
if (!defined('BASE_URL_SERVER')) {
    require_once '../config/config1.php';
}

// Verificar si la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug para verificar datos de sesión
error_log("Sesión ID: " . ($_SESSION['sesion_id'] ?? 'No definido'));
error_log("Token: " . ($_SESSION['sesion_token'] ?? 'No definido'));

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => BASE_URL_SERVER . "src/control/Bien.php?tipo=listarBienes&sesion=" . ($_SESSION['sesion_id'] ?? '') . "&token=" . ($_SESSION['sesion_token'] ?? ''),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json"
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($err) {
    die("cURL Error #:" . $err);
} else {
    $respuesta = json_decode($response);
    
    // Debug: verificar la estructura de la respuesta
    error_log("Respuesta del servidor: " . print_r($respuesta, true));
    
    if (!$respuesta || !isset($respuesta->status)) {
        die("Error: Respuesta inválida del servidor");
    }
    
    if (!$respuesta->status) {
        die("Error del servidor: " . ($respuesta->msg ?? 'Error desconocido'));
    }
    
    $bienes = $respuesta->contenido ?? [];

    // Crear el Excel
    $spreadsheet = new Spreadsheet();
    $spreadsheet->getProperties()->setCreator("admin")->setLastModifiedBy("yo")->setTitle("ReporteBienes")->setDescription("yo");
    $activeWorkSheet = $spreadsheet->getActiveSheet();
    $activeWorkSheet->setTitle("Bienes");

    // Estilo en negrita
    $styleArray = [
        'font' => [
            'bold' => true,
        ]
    ];

    // Aplica negrita a la fila 1 (de A1 a R1 si son 18 columnas)
    $activeWorkSheet->getStyle('A1:R1')->applyFromArray($styleArray);

    $headers = [
        'ID',
        'Id ingreso bienes',
        'id ambiente',
        'cod patrimonial',
        'denominacion',
        'marca',
        'Modelo',
        'tipo',
        'Color',
        'serie',
        'dimensiones',
        'valor',
        'situacion',
        'estado conservacion',
        'observaciones',
        'fecha registro',
        'usuario registro',
        'estado'
    ];

    // Asignar cabeceras en la fila 1
    foreach ($headers as $i => $header) {
        $columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
        $activeWorkSheet->setCellValue($columna . '1', $header);
    }

    // Llenar los datos
    $row = 2;
    foreach ($bienes as $bien) {
        $atributos = [
            $bien->id ?? '',
            $bien->id_ingreso_bienes ?? '',
            $bien->id_ambiente ?? '',
            $bien->cod_patrimonial ?? '',
            $bien->denominacion ?? '',
            $bien->marca ?? '',
            $bien->modelo ?? '',
            $bien->tipo ?? '',
            $bien->color ?? '',
            $bien->serie ?? '',
            $bien->dimensiones ?? '',
            $bien->valor ?? '',
            $bien->situacion ?? '',
            $bien->estado_conservacion ?? '',
            $bien->observaciones ?? '',
            $bien->fecha_registro ?? '',
            $bien->usuario_registro ?? '',
            $bien->estado ?? ''
        ];

        foreach ($atributos as $i => $valor) {
            $columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $activeWorkSheet->setCellValue($columna . $row, $valor);
        }

        $row++;
    }


    ob_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="reporte_bienes.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
