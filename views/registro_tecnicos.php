<?php
//database
include "../config/database.php";
session_start();

// Comprobar si el usuario está autenticado
if (!isset ($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit(); // Terminar el script después de redireccionar
}

// Validar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y sanitizar datos del formulario
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $usuario = mysqli_real_escape_string($conn, $_POST['usuario']);
    $contrasena = mysqli_real_escape_string($conn, $_POST['contrasena']);
    $rol = mysqli_real_escape_string($conn, $_POST['rol']);

    // Insertar datos en la tabla usuarios
    $sql = "INSERT INTO usuarios (nombre, usuario, contrasena, rol)
            VALUES ('$nombre', '$usuario', '$contrasena', '$rol')";

    if ($conn->query($sql) === TRUE) {
        echo "Registro exitoso";
        header("Location: home.php"); // Redireccionar después del registro exitoso
        exit(); // Terminar el script después de redireccionar
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded shadow-md w-full sm:w-96">
            <h2 class="text-2xl font-semibold mb-6">Registro de Usuarios</h2>
            <form action="registro_tecnicos.php" method="POST">
                <div class="mb-4">
                    <label for="nombre" class="block text-gray-700">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div class="mb-4">
                    <label for="usuario" class="block text-gray-700">Usuario:</label>
                    <input type="text" id="usuario" name="usuario" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div class="mb-4">
                    <label for="contrasena" class="block text-gray-700">Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div class="mb-4">
                    <label for="rol" class="block text-gray-700">Rol:</label>
                    <select id="rol" name="rol"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="admin">Administrador</option>
                        <option value="usuario">Usuario</option>
                    </select>
                </div>
                <div class="flex justify-between">
                    <button type="submit"
                        class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:bg-indigo-700">Registrar</button>
                    <a href="home.php"
                        class="btn btn-warning bg-yellow-500 text-white py-2 px-4 rounded-md hover:bg-yellow-600 focus:outline-none focus:bg-yellow-600">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>