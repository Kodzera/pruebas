<?php
// Incluir el archivo de configuración de la base de datos
include "../config/database.php";

// Incluir el encabezado
include "../config/partials/header.php";
session_start();

$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol']; // Obtener el rol del usuario de la sesión
$idUsuario = $_SESSION['id_usuario']; //Obtener el id del usuario

if (!$idUsuario) {
    header("Location: ../index.php");
}

$idProyecto = $_GET['id'];
$codigo = ''; // Inicializar la variable $codigo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_material = $_POST['nombre_material'];

    // Obtener el código máximo actual en la tabla de materiales
    $stmt_max = $conn->prepare("SELECT MAX(codigo) AS max_codigo FROM materiales");
    $stmt_max->execute();
    $result_max = $stmt_max->get_result();
    $row_max = $result_max->fetch_assoc();
    $max_codigo = $row_max['max_codigo'];

    // Generar un nuevo código único incrementando el máximo actual
    if ($max_codigo === null) {
        // Si no hay materiales en la tabla, empezar desde 1
        $nuevo_codigo = 1;
    } else {
        $nuevo_codigo = intval($max_codigo) + 1;
    }

    $stmt = $conn->prepare("INSERT INTO materiales (codigo, nombre) VALUES (?, ?)");
    $stmt->bind_param("ss", $nuevo_codigo, $nombre_material);

    if ($stmt->execute()) {
        header("Location: informacion_proyecto.php?mensaje=Nuevo+material+agregado&id=$idProyecto");
        exit; // Importante: detener la ejecución del script después de redirigir
    }
}




?>

<body>
    <img src="<?php echo $url ?>images/encabezadoactual.png" width="700" class="img-fluid mb-4" alt="Encabezado">
    <h3 class="mb-4 text-center">Nuevo material que no existe</h3>
    <div class="container mt-5 col-md-4">
        <form class="py-2" action="" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre Material</label>
                <input type="text" class="form-control" id="nombre" name="nombre_material" placeholder="Nombre del nuevo material">
            </div>
            <!-- Campo readonly para mostrar el código generado -->
            <div class="form-group">
                <label for="codigo">Código Generado</label>
                <input type="text" class="form-control" id="codigo" name="codigo" value="<?php echo $codigo; ?>" readonly placeholder="EL CODIGO SE GENERA AUTOMATICAMENTE">
            </div>
            <!-- ------------------------------------------------- -->
            <div class="my-2">
                <button type="submit" class="btn btn-success mr-2">Guardar Material</button>
                <a href="<?php echo isset($_GET['id']) ? 'informacion_proyecto.php?id=' . $_GET['id'] : 'informacion_proyecto.php'; ?>" class="btn btn-danger m-2">Volver</a>
            </div>
        </form>
    </div>
</body>