<?php
// Incluir el archivo de configuración de la base de datos
include "../config/database.php";
session_start();

$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol']; // Obtener el rol del usuario de la sesión
$idUsuario = $_SESSION['id_usuario']; //Obtener el id del usuario

if (!$idUsuario) {
    header("Location: ../index.php");
}

// Verificar si se recibieron materiales seleccionados
if (isset($_POST['materiales']) && !empty($_POST['materiales'])) {
    // Obtener el ID del proyecto de la URL
    $idProyecto = isset($_GET['id']) ? $_GET['id'] : null;

    // Verificar si se proporcionó un ID de proyecto válido
    if ($idProyecto) {
        // Recorrer los materiales seleccionados
        foreach ($_POST['materiales'] as $material) {
            // Insertar el material seleccionado en la base de datos
            $sql = "INSERT INTO materialesproyecto (idProyecto, codigoMaterial) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $idProyecto, $material);
            $stmt->execute();
        }

        echo "Los materiales seleccionados se han guardado correctamente en la base de datos.";
    } else {
        echo "No se proporcionó un ID de proyecto válido.";
    }
} else {
    echo "No se seleccionaron materiales para guardar.";
}
