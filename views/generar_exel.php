<?php

require_once '../config/database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

// Obtener el ID de la inspección seleccionada
$idInspeccion = isset($_POST['id_inspeccion']) ? intval($_POST['id_inspeccion']) : null;

if ($idInspeccion) {
    // Consultar el nombre de la inspección y el nombre del técnico
    $sql_inspeccion = "SELECT i.*, u.nombre AS nombre_tecnico
                    FROM inspeccion i
                    INNER JOIN usuarios u ON i.usuario_id = u.id
                    WHERE i.id_inspeccion = ?";
    $stmt_inspeccion = $conn->prepare($sql_inspeccion);
    $stmt_inspeccion->bind_param("i", $idInspeccion);
    $stmt_inspeccion->execute();
    $result_inspeccion = $stmt_inspeccion->get_result();

    if ($result_inspeccion->num_rows > 0) {
        $row_inspeccion = $result_inspeccion->fetch_assoc();
        $nombreInspeccion = $row_inspeccion['nombre_inspeccion'];
        $nombreTecnico = $row_inspeccion['nombre_tecnico'];
        $fechaInspeccion = date('d/m/Y', strtotime($row_inspeccion['fecha']));
    } else {
        die('No se encontró la inspección.');
    }

    // Crear instancia de Spreadsheet
    $spreadsheet = new Spreadsheet();

    // Obtener la hoja activa
    $sheet = $spreadsheet->getActiveSheet();

    // Establecer encabezados para la inspección
    $sheet->setCellValue('A1', 'ID de Inspección');
    $sheet->setCellValue('B1', 'Nombre de Inspección');
    $sheet->setCellValue('C1', 'Técnico');
    $sheet->setCellValue('D1', 'Fecha');

    // Escribir los datos de la inspección en la hoja
    $sheet->setCellValue('A2', $idInspeccion);
    $sheet->setCellValue('B2', $nombreInspeccion);
    $sheet->setCellValue('C2', $nombreTecnico);
    $sheet->setCellValue('D2', $fechaInspeccion);

    // Establecer encabezados para los detalles de la inspección
    $sheet->setCellValue('A4', 'Código Producto');
    $sheet->setCellValue('B4', 'Nombre del Material');
    $sheet->setCellValue('C4', 'Cantidad');
    $sheet->setCellValue('D4', 'Unidad');

    // Establecer estilos para las filas de encabezados
    $headerStyle = [
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => '1A3372'],
        ],
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
    ];

    // Aplicar estilos a las filas de encabezados
    $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
    $sheet->getStyle('A4:D4')->applyFromArray($headerStyle);

    // Consultar los detalles de la inspección desde la base de datos
    $sql_detalles = "SELECT di.*, m.nombre AS nombre_material
                    FROM detalle_inspeccion di
                    INNER JOIN materiales m ON di.codigoMaterial = m.codigo
                    WHERE di.id_inspeccion = ?";
    $stmt_detalles = $conn->prepare($sql_detalles);
    $stmt_detalles->bind_param("i", $idInspeccion);
    $stmt_detalles->execute();
    $result_detalles = $stmt_detalles->get_result();

    // Escribir los detalles en la hoja
    $row = 5; // Comenzar en la fila 5 después de los encabezados
    while ($row_detalle = $result_detalles->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $row_detalle['codigoMaterial']);
        $sheet->setCellValue('B' . $row, $row_detalle['nombre_material']);
        $sheet->setCellValue('C' . $row, $row_detalle['cantidad']);
        $sheet->setCellValue('D' . $row, $row_detalle['unidad']);
        $row++;
    }

    // Ajustar el ancho de las columnas automáticamente
    foreach (range('A', 'D') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Configurar el tipo de contenido y el nombre del archivo
    $nombre_archivo = $nombreInspeccion . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $nombre_archivo . '"');
    header('Cache-Control: max-age=0');

    // Salida del archivo
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    // Cerrar el statement y la conexión
    $stmt_detalles->close();
    $stmt_inspeccion->close();
    $conn->close();
} else {
    echo "ID de inspección no válido.";
}
?>
