<?php
session_start();

// Verificar si se recibió la variable de solo lectura
if (isset($_POST['readonly'])) {
    // Establecer el estado de solo lectura en la sesión
    $_SESSION['readonly'] = $_POST['readonly'] === 'true' ? true : false;
}
?>
