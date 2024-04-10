<?php
// Incluir archivo de configuración de la base de datos
include "../../config/database.php";

// Verificar si se recibió el ID de la inspección
if (isset($_POST['id_inspeccion'])) {
    // Obtener el ID de la inspección
    $idInspeccion = $_POST['id_inspeccion'];

    // Iniciar una transacción
    $conn->begin_transaction();

    // Insertar el ID de la inspección en la tabla proyecto_aceptado
    $insertSql = "INSERT INTO proyecto_aceptado (id_inspeccion) VALUES (?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("i", $idInspeccion);
    $insertResult = $insertStmt->execute();
    
    // Actualizar el campo aprobado_inspeccion a 1 en la tabla inspeccion
    $updateSql = "UPDATE inspeccion SET aprobado_inspeccion = 1 WHERE id_inspeccion = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("i", $idInspeccion);
    $updateResult = $updateStmt->execute();

    // Verificar si ambas consultas fueron exitosas
    if ($insertResult && $updateResult) {
        // Confirmar la transacción
        $conn->commit();
        // Redirigir a alguna página o enviar alguna respuesta
        echo "La inspección fue aprobada y registrada correctamente.";
    } else {
        // Revertir la transacción en caso de error
        $conn->rollback();
        echo "Hubo un error al aprobar la inspección. Por favor, inténtalo de nuevo.";
    }

    // Cerrar las declaraciones preparadas
    $insertStmt->close();
    $updateStmt->close();
}
?>
