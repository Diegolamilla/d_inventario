<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$ruta = explode("/", $_GET['views']);
if (!isset($ruta[1]) || $ruta[1] == "") {
    header("location: " . BASE_URL . "Reporte-bienes");
}

$curl = curl_init(); //inicia la sesión cURL
curl_setopt_array($curl, array(
    CURLOPT_URL => BASE_URL_SERVER . "src/control/Bien.php?tipo=listar_bienes_ordenados_tabla&sesion=" . $_SESSION['sesion_id'] . "&token=" . $_SESSION['sesion_token'] . "&data=" . $ruta[1], //url a la que se conecta
    CURLOPT_RETURNTRANSFER => true, //devuelve el resultado como una cadena del tipo curl_exec
    CURLOPT_FOLLOWLOCATION => true, //sigue el encabezado que le envíe el servidor
    CURLOPT_ENCODING => "", // permite decodificar la respuesta y puede ser"identity", "deflate", y "gzip", si está vacío recibe todos los disponibles.
    CURLOPT_MAXREDIRS => 10, // Si usamos CURLOPT_FOLLOWLOCATION le dice el máximo de encabezados a seguir
    CURLOPT_TIMEOUT => 30, // Tiempo máximo para ejecutar
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, // usa la versión declarada
    CURLOPT_CUSTOMREQUEST => "GET", // el tipo de petición, puede ser PUT, POST, GET o Delete dependiendo del servicio
    CURLOPT_HTTPHEADER => array(
        "x-rapidapi-host: " . BASE_URL_SERVER,
        "x-rapidapi-key: XXXX"
    ), //configura las cabeceras enviadas al servicio
)); //curl_setopt_array configura las opciones para una transferencia cURL

$response = curl_exec($curl); // respuesta generada
$err = curl_error($curl); // muestra errores en caso de existir

curl_close($curl); // termina la sesión 

if ($err) {
    echo "cURL Error #:" . $err; // mostramos el error
} else {
    $respuesta = json_decode($response);

require './vendor/autoload.php';


$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()->setCreator("yp")->setLastModifiedBy("yo")->setTitle("yo")->setDescription("yo");

$activeWorksheet = $spreadsheet->getActiveSheet();
$activeWorksheet->setTitle("hoja 1");
/* $activeWorksheet->setCellValue('A1', 'Hola Mundo!');
$activeWorksheet->setCellValue('A2', 'DNI');
for ($i = 1; $i <= 30; $i++) {
    $activeWorksheet->setCellValue('A' . $i, $i);
}
for ($i = 1; $i <= 30; $i++) {
    // chr(64 + $i) convierte 1->A, 2->B, ..., 26->Z, 27->AA, etc.
    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
    $activeWorksheet->setCellValue($col . '1', $i);
} */

$n1 = 1;
for ($n2=1; $n2 <= 12; $n2++) { 
    $activeWorksheet->setCellValue('A' . $n2, $n1);
    $activeWorksheet->setCellValue('B' . $n2, 'X');
    $activeWorksheet->setCellValue('C' . $n2, $n2);
    $activeWorksheet->setCellValue('D' . $n2, '=');
    $activeWorksheet->setCellValue('E' . $n2, $n1*$n2); 
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="hello_world.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
}
?>