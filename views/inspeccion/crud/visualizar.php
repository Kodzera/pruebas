<?php
// Incluir archivo de configuración de la base de datos
include "../../../config/database.php";
// Incluir el encabezado
include "../../../config/partials/header.php";

session_start();
// Verificar la sesión del usuario
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit(); // Asegurar que se detenga la ejecución después de redireccionar
}

// Obtener datos de la sesión
$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];
$idUsuario = $_SESSION['id_usuario'];

// Variable para almacenar el ID de la inspeccion
$idInspeccion = isset($_GET['id']) ? $_GET['id'] : null;

// Variable para almacenar mensajes de error o éxito
$mensaje = '';
$tipoMensaje = '';
$readonly = '';

// Variable para determinar si se han guardado los materiales
$guardado = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se recibió el ID del proyecto y al menos un campo de cantidad
    if (isset($_POST['idProyecto']) && !empty($_POST['idProyecto'])) {
        $idInspeccion = $_POST['idProyecto'];
        $materiales = array();

        // Recorrer los campos de cantidad
        foreach ($_POST as $key => $value) {
            // Verificar si el campo es de cantidad (tiene el prefijo "cantidad_")
            if (strpos($key, 'cantidad_') !== false) {
                // Obtener el código de material desde el nombre del campo
                $codigoMaterial = substr($key, strlen('cantidad_'));

                // Guardar el código de material y la cantidad en el array de materiales
                $materiales[$codigoMaterial]['cantidad'] = $value;
            }
        }

        // Procesar el campo de observación
        $observacion = isset($_POST['observacion']) ? $_POST['observacion'] : null;

        // Verificar si ya existe una observación para la inspección
        $sql_select_obs = "SELECT id FROM observaciones_inspeccion WHERE id_inspeccion = ?";
        $stmt_select_obs = $conn->prepare($sql_select_obs);
        $stmt_select_obs->bind_param("i", $idInspeccion);
        $stmt_select_obs->execute();
        $result_select_obs = $stmt_select_obs->get_result();

        if ($result_select_obs->num_rows > 0) {
            // Si ya existe una observación, actualizarla
            $sql_update_observacion = "UPDATE observaciones_inspeccion SET observacion = ?, fecha = NOW() WHERE id_inspeccion = ?";
            $stmt_update_observacion = $conn->prepare($sql_update_observacion);
            $stmt_update_observacion->bind_param("si", $observacion, $idInspeccion);
            $stmt_update_observacion->execute();
        } else {
            // Si no existe una observación, insertarla
            $sql_insert_observacion = "INSERT INTO observaciones_inspeccion (id_inspeccion, observacion, fecha) VALUES (?, ?, NOW())";
            $stmt_insert_observacion = $conn->prepare($sql_insert_observacion);
            $stmt_insert_observacion->bind_param("is", $idInspeccion, $observacion);
            $stmt_insert_observacion->execute();
        }


        // Insertar o actualizar los datos en la tabla de la base de datos
        foreach ($materiales as $codigoMaterial => $materialData) {
            // Obtener los datos del material
            $cantidad = $materialData['cantidad'];
            $unidad = isset($_POST['unidad_' . $codigoMaterial]) ? $_POST['unidad_' . $codigoMaterial] : null;

            // Verificar si el material ya existe para el proyecto
            $sql_select = "SELECT cantidad FROM detalle_inspeccion WHERE id_inspeccion = ? AND codigoMaterial = ?";
            $stmt_select = $conn->prepare($sql_select);
            $stmt_select->bind_param("ii", $idInspeccion, $codigoMaterial);
            $stmt_select->execute();
            $result_select = $stmt_select->get_result();

            if ($result_select->num_rows > 0) {
                // Si el material ya existe para el proyecto, actualizar la cantidad y unidad
                $sql_update = "UPDATE detalle_inspeccion SET cantidad = ?, unidad = ? WHERE id_inspeccion = ? AND codigoMaterial = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("isii", $cantidad, $unidad, $idInspeccion, $codigoMaterial);
                $stmt_update->execute();
            } else {
                // Si el material no existe para el proyecto, insertarlo
                $sql_insert = "INSERT INTO detalle_inspeccion (id_inspeccion, codigoMaterial, cantidad, unidad) VALUES (?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("iiis", $idInspeccion, $codigoMaterial, $cantidad, $unidad);
                $stmt_insert->execute();
            }
        }

        // Marcar como guardado
        $guardado = true;

        // Mostrar mensaje de éxito utilizando alerta Bootstrap
        $tipoMensaje = 'success';
        $mensaje = '¡Se guardó correctamente ahora puede generar el PDF!';
        $readonly = 'readonly';
    } else {
        $readonly = '';
        $mensaje = "No se proporcionó un ID de proyecto válido o cantidad.";
        $tipoMensaje = 'error';
    }
}
// $readonly = $guardado ? 'readonly' : '';  // Establecer readonly si $guardado es true

// Consultar información de la inspección
if (!empty($idInspeccion)) {
    $sql_proyecto = "SELECT i.*, s.nombre_servicio 
                     FROM inspeccion i 
                     INNER JOIN servicio s ON i.servicio_id = s.id 
                     WHERE i.id_inspeccion = ?";

    $stmt_proyecto = $conn->prepare($sql_proyecto);
    $stmt_proyecto->bind_param("i", $idInspeccion);
    $stmt_proyecto->execute();
    $result_proyecto = $stmt_proyecto->get_result();

    if ($result_proyecto->num_rows > 0) {
        $proyecto = $result_proyecto->fetch_assoc();

        // Definir la variable $num_materiales y establecerla en 0 inicialmente
        $num_materiales = 0;

        // Consultar los materiales asociados a la inspección
        $sql_materiales = "SELECT m.codigo, m.nombre, d.cantidad AS cantidad_detalle, d.unidad 
                           FROM materiales m 
                           INNER JOIN materialesinspeccion mp ON m.codigo = mp.codigoMaterial 
                           LEFT JOIN detalle_inspeccion d ON m.codigo = d.codigoMaterial AND d.id_inspeccion = ?
                           WHERE mp.id_inspeccion = ?";

        $stmt_materiales = $conn->prepare($sql_materiales);
        $stmt_materiales->bind_param("ii", $idInspeccion, $idInspeccion);
        $stmt_materiales->execute();
        $result_materiales = $stmt_materiales->get_result();

        // Contar el número de materiales obtenidos
        $num_materiales = $result_materiales->num_rows;

        // Consultar la observación de la inspección desde la tabla observaciones_inspeccion
        $sql_observacion_inspeccion = "SELECT observacion FROM observaciones_inspeccion WHERE id_inspeccion = ?";
        $stmt_observacion_inspeccion = $conn->prepare($sql_observacion_inspeccion);
        $stmt_observacion_inspeccion->bind_param("i", $idInspeccion);
        $stmt_observacion_inspeccion->execute();
        $result_observacion_inspeccion = $stmt_observacion_inspeccion->get_result();

        $observacion_inspeccion = null;
        if ($result_observacion_inspeccion->num_rows > 0) {
            $row_observacion_inspeccion = $result_observacion_inspeccion->fetch_assoc();
            $observacion_inspeccion = $row_observacion_inspeccion['observacion'];
        }
    }
}

?>

<body class="d-flex flex-column h-100">
    <img src="<?php echo $url ?>images/encabezadoactual.png" width="700" height="150px" class="logo-redinco">
    <div class="container container-fluid d-flex justify-content-center align-item-center mb-5">
        <div class="border border-success border-3 p-3 mb-2 rounded mt-2">
            <h1>Información de la Inspección</h1>
            <p><strong>Id de la Inspección:</strong>
                <?php echo $proyecto['id_inspeccion']; ?>
            </p>
            <p><strong>Nombre de la Inspección:</strong>
                <?php echo $proyecto['nombre_inspeccion']; ?>
            </p>
            <p><strong>Descripción de la Inspección:</strong>
                <?php echo $proyecto['descripcion_inspeccion']; ?>
            </p>
            <p><strong>Servicio de la Inspección:</strong>
                <?php echo $proyecto['nombre_servicio']; ?>
            </p>
        </div>
    </div>
    <!-- Alerta de mensaje -->
    <?php if (!empty($mensaje)) : ?>
        <div class="d-flex justify-content-center">
            <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Contenedor de botones -->
    <div class="container container-fluid d-flex justify-content-evenly contenedor-botones">
        <a href="../index.php" class="btn btn-warning mb-2">Volver</a>
        <a href="materiales_inspeccion.php?id=<?php echo $idInspeccion; ?>" class="btn btn-dark mb-2">Ver Lista de Materiales</a>

        <!-- Agregar nuevo producto que no exista en la base -->
        <a href="new_material.php?id=<?php echo $idInspeccion; ?>" class="btn btn-dark mb-2">Agregar Nuevo Material</a>

        <?php
        // Verificar si hay materiales para habilitar o deshabilitar los botones
        if ($num_materiales > 0) {
            echo '<a href="generarpdf.php?id=' . $idInspeccion . '&nombre_proyecto=' . urlencode($proyecto["nombre_inspeccion"]) . '" class="btn btn-danger mb-2" target="_blank">Generar PDF</a>';

            echo '<form action="generarexcel.php" method="post">';
            echo '<input type="hidden" name="id_inspeccion" value="' . $idInspeccion . '">';
            echo '<button type="submit" class="btn btn-success mb-2">Generar EXCEL</button>';
            echo '</form>';
        } else {
            echo '<button class="btn btn-danger mb-2" disabled>Generar PDF</button>';
            echo '<button class="btn btn-success mb-2" disabled>Generar EXCEL</button>';
        }
        ?>

    </div>

    <!-- Formulario para ingresar cantidades -->
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <input type="hidden" name="idProyecto" value="<?php echo $idInspeccion; ?>">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover mt-4 container">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" class="hide-on-tablet">Código</th>
                        <th scope="col">Material</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Unidad</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($num_materiales > 0) : ?>
                        <?php
                        // Array para almacenar los códigos de material ya procesados
                        $codigos_procesados = array();

                        // Mostrar los materiales en la tabla
                        while ($row = $result_materiales->fetch_assoc()) {
                            $codigo = stripslashes($row['codigo']);
                            $nombre = stripslashes($row['nombre']);
                            $cantidad_detalle = $row['cantidad_detalle'];
                            $unidad_detalle = $row['unidad'];

                            // Verificar si el código de material ya fue procesado
                            if (!in_array($codigo, $codigos_procesados)) {
                                // Agregar el código a la lista de códigos procesados
                                $codigos_procesados[] = $codigo;

                                // Mostrar la fila en la tabla
                        ?>
                                <tr>
                                    <td class="hide-on-tablet">
                                        <?php echo $codigo; ?>
                                    </td>
                                    <td>
                                        <?php echo $nombre; ?>
                                    </td>
                                    <td>
                                        <!-- Campo de entrada para la cantidad -->
                                        <input type="number" min="0" autofocus name="cantidad_<?php echo $codigo; ?>" class="form-control" value="<?php echo $cantidad_detalle; ?>" <?php echo $readonly; ?> required>

                                    </td>
                                    <td>
                                        <!-- Campo de entrada para la unidad -->
                                        <input type="text" name="unidad_<?php echo $codigo; ?>" class="form-control" value="<?php echo $unidad_detalle ? $unidad_detalle : 'Und'; ?>" placeholder="Und" required <?php echo $readonly; ?>>


                                    </td>
                                    <td>
                                        <!-- Botón "Editar" -->
                                        <button type="button" class="btn btn-warning editar-cantidad" data-codigo="<?php echo $codigo; ?>">Editar</button>
                                    </td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">No hay materiales asociados a esta Inspección👍.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <style>
            /* Estilos adicionales si es necesario */
            textarea {
                resize: none;
                /* Para evitar que el textarea sea redimensionable */
                border: 2px solid #000;
            }
        </style>

        <div class="container">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="observacion">
                        <label for="observacion">Observación:</label>
                        <!-- Agregar el atributo readonly al textarea de observación -->
                        <textarea name="observacion" id="observacion" class="form-control" rows="3" <?php echo $readonly; ?>><?php echo isset($observacion_inspeccion) ? $observacion_inspeccion : ''; ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <!-- Botón para editar la observación -->
        <!-- <div class="text-center">
        <button type="button" id="editarObservacion" class="btn btn-secondary my-2">Editar Observación</button>
    </div> -->

        <!-- Botón "Guardar" -->
        <div class="text-center">
            <?php if ($num_materiales == 0) : ?>
                <button type="submit" class="btn btn-primary my-2" disabled>Guardar</button>
            <?php else : ?>
                <button type="submit" class="btn btn-primary my-2">Guardar</button>
            <?php endif; ?>
        </div>
    </form>

    <!-- Script JavaScript para cambiar entre solo lectura y editable al hacer clic en "Editar" -->
    <script>
        // Script JavaScript para cambiar entre solo lectura y editable al hacer clic en "Editar"
        const botonesEditar = document.querySelectorAll('.editar-cantidad');
        botonesEditar.forEach(boton => {
            boton.addEventListener('click', function() {
                const codigoMaterial = this.getAttribute('data-codigo');
                const inputCantidad = document.querySelector('input[name="cantidad_' + codigoMaterial + '"]');
                const inputUnidad = document.querySelector('input[name="unidad_' + codigoMaterial + '"]');

                // Quitar el atributo 'readonly' para permitir la edición
                inputCantidad.removeAttribute('readonly');
                inputUnidad.removeAttribute('readonly');

                inputCantidad.focus(); // Opcional: enfocar el campo de cantidad automáticamente al hacer clic en "Editar"
            });
        });

        // Bloquear los campos de cantidad y unidad que ya tienen información y mostrar el botón "Editar"
        document.addEventListener("DOMContentLoaded", function() {
            const inputsCantidad = document.querySelectorAll('input[name^="cantidad_"]');
            inputsCantidad.forEach(input => {
                if (input.value !== '') {
                    input.setAttribute('readonly', 'readonly');
                    const codigoMaterial = input.getAttribute('name').replace('cantidad_', '');
                    const inputUnidad = document.querySelector('input[name="unidad_' + codigoMaterial + '"]');
                    inputUnidad.setAttribute('readonly', 'readonly');

                    const botonEditar = document.querySelector('.editar-cantidad[data-codigo="' + codigoMaterial + '"]');
                    botonEditar.style.display = 'inline'; // Mostrar el botón "Editar"
                }
            });
        });
    </script>
</body>

</html>