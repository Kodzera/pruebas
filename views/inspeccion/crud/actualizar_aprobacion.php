<?php
// Incluir archivo de configuración de la base de datos
include "../../../config/database.php";

// Verificar si se ha recibido el parámetro id_inspeccion y aprobar
if (isset($_POST['id_inspeccion']) && isset($_POST['aprobar'])) {
    $idInspeccion = $_POST['id_inspeccion'];

    // Actualizar el valor de aprobado_inspeccion a 1 en la base de datos
    $sql = "UPDATE inspeccion SET aprobado_inspeccion = 1 WHERE id_inspeccion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idInspeccion);

    if ($stmt->execute()) {
        // Éxito al actualizar el estado de aprobación
        echo "El estado de aprobación se ha actualizado correctamente.";
    } else {
        // Error al actualizar el estado de aprobación
        echo "Hubo un error al actualizar el estado de aprobación.";
    }
} else {
    // Si no se reciben los parámetros esperados, mostrar un mensaje de error
    echo "Parámetros incompletos.";
}
?>
