<?php

require './vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Función para obtener bienes desde el controlador
function obtenerBienes() {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => BASE_URL_SERVER . "src/control/Bien.php?tipo=listarBienes&sesion=" . $_SESSION['sesion_id'] . "&token=" . $_SESSION['sesion_token'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        throw new Exception("Error cURL: " . $err);
    }

    $respuesta = json_decode($response);
    
    if (!$respuesta || !isset($respuesta->status) || !$respuesta->status) {
        throw new Exception("Error al obtener datos: " . ($respuesta->msg ?? 'Respuesta inválida'));
    }

    return isset($respuesta->contenido) ? $respuesta->contenido : [];
}

try {
    // Obtener bienes
    $bienes = obtenerBienes();
    
    if (empty($bienes)) {
        die("No hay bienes registrados en la base de datos");
    }

    // Crear el Excel
    $spreadsheet = new Spreadsheet();
    $spreadsheet->getProperties()
        ->setCreator("Sistema de Inventario")
        ->setLastModifiedBy("Sistema de Inventario")
        ->setTitle("Reporte de Bienes")
        ->setDescription("Reporte generado automáticamente");

    $activeWorkSheet = $spreadsheet->getActiveSheet();
    $activeWorkSheet->setTitle("Bienes");

    // Estilo para encabezados
    $styleArray = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => 'E2EFDA',
            ],
        ],
    ];

    $activeWorkSheet->getStyle('A1:V1')->applyFromArray($styleArray);

    // Encabezados completos
    $headers = [
        'ID',
        'ID Ingreso Bienes',
        'ID Ambiente',
        'Código Patrimonial',
        'Denominación',
        'Marca',
        'Modelo',
        'Tipo',
        'Color',
        'Serie',
        'Dimensiones',
        'Valor',
        'Situación',
        'Estado Conservación',
        'Observaciones',
        'Fecha Registro',
        'Usuario Registro',
        'Estado',
        'Ingreso',
        'Ambiente'
    ];

    // Asignar cabeceras
    foreach ($headers as $i => $header) {
        $columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
        $activeWorkSheet->setCellValue($columna . '1', $header);
    }

    // Llenar datos
    $row = 2;
    foreach ($bienes as $bien) {
        $datos = [
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
            $bien->estado ?? '',
            $bien->ingresoname ?? '',
            $bien->ambientename ?? ''
        ];

        foreach ($datos as $i => $valor) {
            $columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $activeWorkSheet->setCellValue($columna . $row, $valor);
        }
        $row++;
    }

    // Autoajustar columnas
    foreach (range(1, count($headers)) as $column) {
        $columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column);
        $activeWorkSheet->getColumnDimension($columna)->setAutoSize(true);
    }

    // Generar archivo
    ob_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="reporte_bienes_' . date('Y-m-d_H-i-s') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
