<?php
require_once '../config/database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

//YAYA MIJO YA 
//BITCH I'M BALL COMO LAMELO
// Crear una instancia de Spreadsheet
$spreadsheet = new Spreadsheet();

// Obtener la hoja activa
$sheet = $spreadsheet->getActiveSheet();

// Definir las cabeceras de las columnas
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Nombre del Proyecto');
$sheet->setCellValue('C1', 'Descripción');
$sheet->setCellValue('D1', 'Nombre del Servicio');
$sheet->setCellValue('E1', 'Fecha');
$sheet->setCellValue('F1', 'Código del Material');
$sheet->setCellValue('G1', 'Nombre del Material');
$sheet->setCellValue('H1', 'Cantidad');

// Realizar la consulta para obtener la información de todos los proyectos y servicios
$sql = "SELECT p.id, p.nombre AS nombre_proyecto, p.descripcion, s.nombre_servicio AS nombre_servicio, p.fecha,
                d.codigoMaterial AS codigo_material, m.nombre AS nombre_material, d.cantidad AS cantidad_material
        FROM proyecto p
        INNER JOIN servicio s ON p.servicio_id = s.id
        LEFT JOIN detalle d ON p.id = d.idProyecto
        LEFT JOIN materiales m ON d.codigoMaterial = m.codigo";

$result = $conn->query($sql);

// Verificar si se encontraron resultados
if ($result->num_rows > 0) {
    // Inicializar la fila a partir de la cual se escribirán los datos en el archivo Excel
    $row = 2;
    // Inicializar el ID del proyecto anterior
    $previous_project_id = null;

    // Escribir los datos en el archivo Excel
    while ($row_data = $result->fetch_assoc()) {
        if ($row_data['id'] !== $previous_project_id) {
            // Escribir la información del proyecto solo en la primera fila de cada proyecto
            $sheet->setCellValue('A' . $row, $row_data['id']);
            $sheet->setCellValue('B' . $row, $row_data['nombre_proyecto']);
            $sheet->setCellValue('C' . $row, $row_data['descripcion']);
            $sheet->setCellValue('D' . $row, $row_data['nombre_servicio']);
            $sheet->setCellValue('E' . $row, $row_data['fecha']);
            // Actualizar el ID del proyecto anterior
            $previous_project_id = $row_data['id'];
        }
        // Escribir los datos del material y cantidad en cada fila
        $sheet->setCellValue('F' . $row, $row_data['codigo_material']);
        $sheet->setCellValue('G' . $row, $row_data['nombre_material']);
        $sheet->setCellValue('H' . $row, $row_data['cantidad_material']);
        
        $row++;
    }

    // Centrar la información en la columna "Código del Material"
    $sheet->getStyle('F2:F' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Ajustar automáticamente el ancho de las columnas
    foreach (range('A', 'H') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Guardar el archivo Excel
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="EXCEL_GENERAL.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} else {
    echo "No se encontraron proyectos con información de servicio asociada.";
}

// Cerrar la conexión
$conn->close();
?>
