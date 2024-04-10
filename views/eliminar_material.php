<?php
// Incluir el archivo de configuración de la base de datos
include "../config/database.php";

// Verificar si se recibió el código de material a eliminar
if (isset($_POST['codigo_material']) && !empty($_POST['codigo_material'])) {
    // Obtener el código de material
    $codigoMaterial = $_POST['codigo_material'];

    // Eliminar el registro correspondiente de la base de datos
    $sql_eliminar = "DELETE FROM detalle WHERE idProyecto = ? AND codigoMaterial = ?";
    $stmt_eliminar = $conn->prepare($sql_eliminar);
    $stmt_eliminar->bind_param("is", $idProyecto, $codigoMaterial);
    $stmt_eliminar->execute();

    // Verificar si se eliminó correctamente el registro
    if ($stmt_eliminar->affected_rows > 0) {
        // La eliminación fue exitosa
        http_response_code(200); // OK
    } else {
        // No se encontró el registro para eliminar
        http_response_code(404); // No encontrado
    }
} else {
    // No se recibió el código de material a eliminar
    http_response_code(400); // Solicitud incorrecta
}
