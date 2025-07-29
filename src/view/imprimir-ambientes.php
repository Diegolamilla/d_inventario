<?php
$curl = curl_init(); //inicia la sesión cURL
curl_setopt_array($curl, array(
  CURLOPT_URL => BASE_URL_SERVER . "src/control/Ambiente.php?tipo=listarAmbientes&sesion=" . $_SESSION['sesion_id'] . "&token=" . $_SESSION['sesion_token'], //url a la que se conecta
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
  $contenido = $respuesta->contenido;
  $contenido_pdf = '';
  $contenido_pdf = '
           <!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lista de ambientes</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
    }

    h2 {
      text-align: center;
      text-transform: uppercase;
    }

    .info {
      margin-bottom: 20px;
    }

    .info p {
      margin: 5px 0;
    }

    .info span.label {
      font-weight: bold;
      display: inline-block;
      width: 100px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    table, th, td {
      border: 1px solid black;
    }

    th, td {
      text-align: center;
      padding: 5px;
    }

    .firma-container {
      margin-top: 80px;
      display: flex;
      justify-content: space-between;
      padding: 0 40px;
    }

    .firma {
      text-align: center;
    }

    .fecha {
      text-align: right;
      margin-top: 30px;
    }
       table {
    border-collapse: collapse;
    width: 100%;
    font-size: 9pt;
  }
  th, td {
    border: 1px solid #000;
    padding: 4px;
    text-align: center;
  }
  thead th {
    background-color: #f2f2f2;
    font-weight: bold;
    }
  .firma {
    width: 100%;
    border: none;
    margin-top: 100px;
    border-collapse: collapse;
  }
  .firma td {
    width: 50%;
    text-align: center;
    border: none;
  }

  </style>
</head>
<body>

  <h2>LISTA DE AMBIENTES</h2>

  <table>
    <thead>
      <tr>
        <th>ITEM</th>
        <th>INSTITUCION</th>
        <th>ENCARGADO</th>
        <th>CODIGO</th>
        <th>DETALLE</th>
        <th>OTROS DETALLES</th>
      </tr>
    </thead>
      <tbody>
      ';

  $contador = 1;
  foreach ($contenido as $ambiente) {
    $contenido_pdf .= "<tr>";
    $contenido_pdf .=  "<td>" . $contador . "</td>";
    $contenido_pdf .=  "<td>" . $ambiente->institucion . "</td>";
    $contenido_pdf .=  "<td>" . $ambiente->encargado . "</td>";
    $contenido_pdf .=  "<td>" . $ambiente->codigo . "</td>";
    $contenido_pdf .=  "<td>" . $ambiente->detalle . "</td>";
    $contenido_pdf .=  "<td>" . $ambiente->otros_detalle . "</td>";
    $contenido_pdf .=  "</tr>";
    $contador += 1;
  }

  $contenido_pdf .= '
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
    $contenido_pdf .= "Ayacucho, $dia de $mes del $anio";
  }

  $contenido_pdf .= '

        </p>
            <table class="firma" style="width:100%; margin-top: 150px;"> <br><br><br><br><br><br>
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

  class MYPDF extends TCPDF
  {
    // Encabezado
    public function Header()
    {
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
    public function Footer()
    {
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

  $pdf = new MYPDF();
  $pdf->SetCreator(PDF_CREATOR);
  $pdf->SetAuthor('admin');
  $pdf->SetTitle('lista de Ambientes');

  $pdf->SetMargins(PDF_MARGIN_LEFT, 45, PDF_MARGIN_RIGHT);
  $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
  $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


  $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

  $pdf->SetFont('helvetica', '', 8);


  $pdf->AddPage();


  $pdf->writeHTML($contenido_pdf, true, false, true, false, '');

  $pdf->Output('lista_ambientes_' . date('Ymd_His') . '.pdf', 'I');
}
