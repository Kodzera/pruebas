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


// Definir una variable para almacenar un mensaje de éxito o error después de actualizar el proyecto
$mensaje = '';

// Verificar si se ha proporcionado un ID de proyecto
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // Obtener el ID del proyecto desde la URL
    $idProyecto = $_GET['id'];

    // Consulta SQL para obtener los datos del proyecto
    $sql = "SELECT * FROM proyecto WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idProyecto);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si se encontró el proyecto
    if ($result->num_rows > 0) {
        // Obtener los datos del proyecto
        $proyecto = $result->fetch_assoc();

        // Verificar si se ha enviado el formulario de edición
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Obtener los datos del formulario
            $nombre = $_POST["nombre"];
            $descripcion = $_POST["descripcion"];

            // Consulta SQL para actualizar la información del proyecto
            $sql = "UPDATE proyecto SET nombre = ?, descripcion = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nombre, $descripcion, $idProyecto);
            if ($stmt->execute()) {
                // Actualizar los datos del proyecto en la variable $proyecto
                $proyecto['nombre'] = $nombre;
                $proyecto['descripcion'] = $descripcion;
                $mensaje = 'La información del proyecto se actualizó correctamente.';
                header("Location: home.php");
            } else {
                $mensaje = 'Error al actualizar la información del proyecto.';
            }
        }
?>

        <body class="d-flex flex-column h-100">
            <img src="<?php echo $url ?>images/encabezadoactual.png" width="700">
            <div class="container d-flex justify-content-center align-item-center">
                <div class="">

                    <!-- Mostrar mensaje de éxito o error después de actualizar el proyecto -->
                    <?php if (!empty($mensaje)) : ?>
                        <div class="alert alert-<?php echo ($stmt->error) ? 'danger' : 'success'; ?>" role="alert">
                            <?php echo $mensaje; ?>
                        </div>
                    <?php endif; ?>

                    <h2>Editar Información del Proyecto</h2>
                    <!-- Formulario para editar la información del proyecto -->
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="nombre">Nombre del Proyecto:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $proyecto['nombre']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción del Proyecto:</label>
                            <textarea class="form-control" id="descripcion" name="descripcion"><?php echo $proyecto['descripcion']; ?></textarea>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            <a href="<?php echo isset($_GET['id']) ? 'informacion_proyecto.php?id=' . $_GET['id'] : 'informacion_proyecto.php'; ?>" class="btn btn-warning m-2">Volver</a>
                        </div>

                    </form>
                </div>
            </div>
        </body>


        </html>
<?php
    } else {
        echo "<p>No se encontró el proyecto.</p>";
    }
} else {
    echo "<p>No se proporcionó un ID de proyecto válido.</p>";
}
?>