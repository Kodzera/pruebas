<?php
session_start();
date_default_timezone_set('America/Los_Angeles');

// Incluir Dompdf
require_once '../config/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Incluir la configuración de la base de datos
require_once '../config/database.php';

// Crear instancia de Dompdf con opciones
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

// Obtener el ID y el nombre de la inspección del parámetro GET
$idInspeccion = isset($_GET['id']) ? intval($_GET['id']) : null;

// Verificar si el ID de la inspección es válido
if ($idInspeccion <= 0) {
    die('ID de inspección no válido.');
}

// Consulta SQL para obtener los detalles de la inspección y el nombre del técnico que la realizó
$sql_inspeccion = "SELECT i.*, u.nombre AS nombre_tecnico
                    FROM inspeccion i
                    INNER JOIN usuarios u ON i.usuario_id = u.id
                    WHERE i.id_inspeccion = ?";

$stmt_inspeccion = $conn->prepare($sql_inspeccion);
$stmt_inspeccion->bind_param("i", $idInspeccion);
$stmt_inspeccion->execute();
$result_inspeccion = $stmt_inspeccion->get_result();

// Verificar si se encontró la inspección
if ($result_inspeccion->num_rows > 0) {
    $row_inspeccion = $result_inspeccion->fetch_assoc();
    $nombreInspeccion = $row_inspeccion['nombre_inspeccion'];
} else {
    die('No se encontró la inspección.');
}

// Contenido HTML para el PDF
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body {
    font-family: "Arial", sans-serif;
    background-color: #f0f0f0;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff; /* Cambiar el color de fondo */
    border-radius: 10px;
    box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
}
.table-container {
    margin-top: 20px;
    background-color: transparent; /* Cambiar el color de fondo */
}
table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
    background-color: #fff; /* Cambiar el color de fondo */
}
th, td {
    border: 1px solid black;
    padding: 10px;
    background-color: #fff; /* Cambiar el color de fondo */
}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="../images/encabezadoactual.png" alt="Encabezado">
        <h2>Detalles de la Inspección: ' . $nombreInspeccion . '</h2>
    </div>';

// Consulta SQL para obtener los detalles de la inspección y el nombre del técnico que la realizó
$sql = "SELECT di.*, m.nombre, u.nombre AS nombre_tecnico
        FROM detalle_inspeccion di
        INNER JOIN materiales m ON di.codigoMaterial = m.codigo
        INNER JOIN inspeccion i ON di.id_inspeccion = i.id_inspeccion
        INNER JOIN usuarios u ON i.usuario_id = u.id
        WHERE di.id_inspeccion = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idInspeccion);
$stmt->execute();
$result = $stmt->get_result();

// Obtener todos los resultados en un array
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Obtener el nombre del técnico que realizó la inspección
$nombreTecnico = !empty($rows) ? $rows[0]['nombre_tecnico'] : '';

$html .= '
    <p><strong>ID de la Inspección:</strong> ' . $idInspeccion . '</p>
    <p><strong>Técnico:</strong> ' . $nombreTecnico . '</p>
    <p><strong>Fecha:</strong> ' . date('d/m/Y') . '</p>
    <div class="table-container">
        <table>
        <tr>
        <th>Código</th>
        <th>Material</th>
        <th>Cantidad</th>
        <th>Unidad</th>
        </tr>';

// Recorrer los resultados almacenados en el array y agregar filas a la tabla HTML
foreach ($rows as $row) {
    // Verificar si la cantidad es diferente de cero (0)
    if ($row['cantidad'] != 0) {
        $html .= '
        <tr>
        <td>' . $row['codigoMaterial'] . '</td>
        <td>' . $row['nombre'] . '</td>
        <td>' . $row['cantidad'] . '</td>
        <td>' . $row['unidad'] . '</td>
        </tr>';
    }
}

$html .= '
        </table>
    </div>';

// Consulta SQL para obtener la observación de la inspección
$sql_observacion = "SELECT observacion FROM observaciones_inspeccion WHERE id_inspeccion = ?";
$stmt_observacion = $conn->prepare($sql_observacion);
$stmt_observacion->bind_param("i", $idInspeccion);
$stmt_observacion->execute();
$result_observacion = $stmt_observacion->get_result();

// Obtener la observación de la inspección si existe
$observacion = '';
if ($result_observacion->num_rows > 0) {
    $row_observacion = $result_observacion->fetch_assoc();
    $observacion = $row_observacion['observacion'];
}

$html .= '
    <p><strong>Observación:</strong> ' . $observacion . '</p>
</div>
</body>
</html>';
// Cargar el contenido HTML en DOMPDF
$dompdf->loadHtml($html);

// Renderizar el PDF
$dompdf->render();

// Generar el PDF en la salida con nombre de archivo personalizado
$dompdf->stream("$nombreInspeccion.pdf", array("Attachment" => false));

// Después de renderizar el PDF
echo json_encode(array("success" => true)); 
