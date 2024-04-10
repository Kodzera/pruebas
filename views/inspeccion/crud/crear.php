<?php
// Incluir archivo de configuración de la base de datos
include "../../../config/database.php";
// Incluir el encabezado
include "../../../config/partials/header.php";
// Iniciar sesión
session_start();

// Obtener datos del usuario de la sesión
$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];
$idUsuario = $_SESSION['id_usuario'];

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit; // Detener la ejecución del script
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_inspeccion = $_POST['nombre_inspeccion'];
    $descripcion_inspeccion = $_POST['descripcion_inspeccion'];
    $servicioId = $_POST['servicio'];

    // Valor por defecto para el campo aprobado_inspeccion
    $aprobado_inspeccion = 0; // Por defecto se establece a falso

    // Preparamos la consulta para insertar
    $stmt = $conn->prepare("INSERT INTO inspeccion(nombre_inspeccion, descripcion_inspeccion, aprobado_inspeccion, usuario_id, servicio_id) VALUES (?, ?, ?, ?, ?)");

    $stmt->bind_param("ssiii", $nombre_inspeccion, $descripcion_inspeccion, $aprobado_inspeccion, $idUsuario, $servicioId);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Inspección creada correctamente";
        $_SESSION['color'] = "success";
        header('Location: ../index.php');
        exit;
    } else {
        $_SESSION['msg'] = "Error al crear la inspección";
        $_SESSION['color'] = "danger";
        header('Location: ../index.php');
        exit;
    }
    

}
?>

<body class="d-flex flex-column h-100">
    <style>

    </style>
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-4">
                <img src="<?php echo $url ?>images/encabezadoactual.png" class="img-fluid mb-4" alt="Encabezado">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Añadir Nueva Inspección</h2>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="nombre">Nombre de la Inspección:</label>
                                <input type="text" class="form-control" id="nombre" name="nombre_inspeccion" required autofocus>
                            </div>

                            <div class="form-group mb-3">
                                <label for="descripcion">Descripción:</label>
                                <textarea class="form-control" id="descripcion" name="descripcion_inspeccion" rows="4" required></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label for="servicio" class="form-label">Servicio</label>
                                <select name="servicio" id="servicio" class="form-select">
                                    <?php 
                                    // Consultar los servicios disponibles
                                    $queryServicios = "SELECT id, nombre_servicio FROM servicio";
                                    $resultServicios = $conn->query($queryServicios);

                                    // Verificar si se encontraron resultados
                                    if ($resultServicios->num_rows > 0) {
                                        // Iterar sobre cada fila de resultados y mostrar los servicios en el menú desplegable
                                        while ($row = $resultServicios->fetch_assoc()) {
                                            echo '<option value="' . $row['id'] . '">' . $row['nombre_servicio'] . '</option>';
                                        }
                                    } else {
                                        echo '<option value="" disabled>No se encontraron servicios</option>';
                                    }
                                    ?>
                                </select>
                            </div>


                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary btn-block">Crear Inspección</button>
                                <a href="../index.php" class="btn btn-warning">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>