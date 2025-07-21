<?php
$ruta = explode("/", $_GET['views']);
if (!isset($ruta[1]) || $ruta[1] == "") {
    header("location: " . BASE_URL . "movimientos");
}

$curl = curl_init(); //inicia la sesión cURL
curl_setopt_array($curl, array(
    CURLOPT_URL => BASE_URL_SERVER . "src/control/Movimiento.php?tipo=buscar_movimiento_id&sesion=" . $_SESSION['sesion_id'] . "&token=" . $_SESSION['sesion_token'] . "&data=" . $ruta[1], //url a la que se conecta
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
    /*print_r($respuesta); */ // mostramos la respuesta
    $contenido_pdf = ''; 
    $contenido_pdf .= '
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Papeleta de Rotación de Bienes</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 40px;
            }

            h1 {
                text-align: center;
                font-weight: bold;
            }
 
            .datos {
                margin-bottom: 20px;
            }

            .datos p {
                margin: 5px 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 40px;
            }

            table,
            th,
            td {
                border: 1px solid black;
            }

            th,
            td {
                padding: 8px;
                text-align: center;
            }

            .firmas {
                display: flex;
                justify-content: space-around;
                align-items: center;
                margin-top: 40px;
            }

            .firma {
                text-align: center;
            }
        </style>
    </head>

    <body>

        <h1>PAPELETA DE ROTACION DE BIENES</h1>
    
        <br>
        <div class="datos">
       
            <p><strong>ENTIDAD:</strong> DIRECCION REGIONAL DE EDUCACION - AYACUCHO</p>
            <p><strong>AREA:</strong> OFICINA DE ADMINISTRACIÓN</p>
            <p><strong>ORIGEN:</strong> '. $respuesta->amb_origen->codigo . ' - ' . $respuesta->amb_origen->detalle .'</p>
            <p><strong>DESTINO:</strong> '. $respuesta->amb_destino->codigo . ' - ' . $respuesta->amb_destino->detalle .'</p>
            <p><strong>MOTIVO (*):</strong> '. $respuesta->movimiento->descripcion .'</p>
        </div>
    
        <table>
            <thead>
                <tr>
                    <th>Nº</th>
                    <th>CODIGO PATRIMONIAL</th>
                    <th>NOMBRE DEL BIEN</th>
                    <th>MARCA</th>
                    <th>COLOR</th>
                    <th>MODELO</th>
                    <th>ESTADO</th>
                </tr>
            </thead>
            <tbody>
    ';


    
                
                if (isset($respuesta->detalle) && count($respuesta->detalle) > 0) {
                    $i = 1;
                    foreach ($respuesta->detalle as $bien) {
                        $contenido_pdf.= "<tr>";
                        $contenido_pdf.= "<td>" . $i . "</td>";
                        $contenido_pdf.= "<td>" . $bien->cod_patrimonial . "</td>";
                        $contenido_pdf.= "<td>" . $bien->denominacion . "</td>";
                        $contenido_pdf.= "<td>" . $bien->marca . "</td>";
                        $contenido_pdf.= "<td>" . $bien->color . "</td>";
                        $contenido_pdf.= "<td>" . $bien->modelo . "</td>";
                        $contenido_pdf.= "<td>" . $bien->estado . "</td>";
                        $contenido_pdf.= "</tr>";
                        $i++;
                    }
                } else {
                    $contenido_pdf.= "<tr><td colspan='7'>No se encontraron bienes para mostrar.</td></tr>";
                }
                $contenido_pdf.= '
            
        
             </tbody>
        </table>

        <p style="text-align: right;">
            
            ';
            // Mostrar la fecha de registro del movimiento en formato "Ayacucho, 18 de abril del 2025"
            if (isset($respuesta->movimiento->fecha_registro) && $respuesta->movimiento->fecha_registro != '') {
                setlocale(LC_TIME, 'es_ES.UTF-8', 'spanish');
                $fecha = strtotime($respuesta->movimiento->fecha_registro);
                // Si no funciona setlocale en el servidor, usar un array de meses en español
                $meses = [
                    1 => 'enero',
                    2 => 'febrero',
                    3 => 'marzo',
                    4 => 'abril',
                    5 => 'mayo',
                    6 => 'junio',
                    7 => 'julio',
                    8 => 'agosto',
                    9 => 'septiembre',
                    10 => 'octubre',
                    11 => 'noviembre',
                    12 => 'diciembre'
                ];
                $dia = date('d', $fecha);
                $mes = $meses[(int)date('m', $fecha)];
                $anio = date('Y', $fecha);
                $contenido_pdf.= "Ayacucho, $dia de $mes del $anio";
            }
            
    $contenido_pdf .= '

        </p>
            <table style="width:100%; border: none; margin-top: 100px;"> <br><br><br><br><br><br>
            <tr>
                <td style="width: 50%; text-align: center;">
                ------------------------------<br>
                ENTREGUÉ CONFORME
                </td>
                <td style="width: 50%; text-align: center;">
                ------------------------------<br>
                RECIBÍ CONFORME
                </td>
            </tr>
            </table>

    </body>

    </html>       
    ';



require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');

class MYPDF extends TCPDF {
    // Encabezado
    public function Header() {
        $this->SetXY(10, 10);
        $logo_left  = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSABjBsifx4kJK7C6ewR1dqJ8DGpEoKk6McLQ&s';
        $logo_right = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT_BSnUuKJh7yQ05Oav2g2R4W3L0o99TfFS-A&s';

       $this->Image($logo_left, 15, 10, 38, 30); 
      $this->Image($logo_right, 170, 10, 25, 25);
       
        // Logo
        
        // Título
        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(0, 5, 'GOBIERNO REGIONAL DE AYACUCHO', 0, 1, 'C');
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(0, 5, 'DIRECCION REGIONAL DE EDUCACION - AYACUCHO', 0, 1, 'C');
        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(0, 5, 'DIRECCION DE ADMINISTRACION', 0, 1, 'C');
        $this->Ln(2);
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(0, 5, 'ANEXO N° 01', 0, 3, 'C');
        $this->SetXY(10, 10);
    }

    // Pie de página
    public function Footer() {
         $this->SetXY(75, -25);

    // Fuente para el número de página
    $this->SetFont('helvetica', 'B', 7);
    $this->Cell(0, 10, 'https://www.dreaya.gob.pe', 0, 0, 'C');

    // Fuente para el contenido de contacto
    $this->SetFont('helvetica', '', 7);

    // Posiciona el texto de contacto a la derecha
    $this->SetXY(115, -25); // Ajusta X e Y según necesidad
    $html = '
    <table cellpadding="1" cellspacing="0">
        <tr>
            <td rowspan="1" style="color:#E48187;"><b>|</b></td>
            <td>Jr. 28 de Julio Nº 383 – Huamanga</td>
        </tr>
        <tr>
        <td rowspan="1" style="color:#E48187;"><b>|</b></td>
            <td><i>&#9742;</i> (066) 31-2364</td>
        </tr>
        <tr>
        <td rowspan="1" style="color:#E48187;"><b>|</b></td>
            <td><i>&#128224;</i>; (066) 31-1395 Anexo 55001</td>
        </tr>
    </table>';

    $this->writeHTMLCell(0, 0, '', '', $html, 0, 1, false, true, 'R', true);
    }
}
// Crear un nuevo PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema de Inventario');
$pdf->SetTitle('Reporte de Movimiento de Bienes');
// asignar margenes
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
// asigna salto de página automatico
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// asignar la fuente
$pdf->SetFont('helvetica', 'B', 8);
// Añadir una página
$pdf->AddPage();
$pdf->writeHTML($contenido_pdf, true, false, true, false, '');
ob_clean();
// Cerrar y enviar el PDF al navegador
$pdf->Output('papeleta_movimiento.pdf', 'I'); // 'I' para mostrar en el navegador, 'D' para descargar directamente

}
