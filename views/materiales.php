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

// Variable para almacenar el término de búsqueda
$busqueda = "";

// Variable para almacenar el ID del proyecto
$idProyecto = isset($_GET['id']) ? $_GET['id'] : null;

// Realizar la consulta SQL para obtener la lista de materiales
$sql = "SELECT * FROM materiales";
$result = $conn->query($sql);

// Verificar si se envió el formulario para guardar la selección de materiales
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se recibieron materiales seleccionados
    if (isset($_POST['materiales']) && !empty($_POST['materiales']) && $idProyecto) {
        // Recorrer los materiales seleccionados
        foreach ($_POST['materiales'] as $material) {
            // Insertar el material seleccionado en la base de datos
            $sql = "INSERT INTO materialesinspeccion (id_inspeccion, codigoMaterial) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $idProyecto, $material);
            $stmt->execute();
        }

        echo "Se generó completamente";
        header("Location: informacion_proyecto.php?id=$idProyecto"); // Redirige con el ID del proyecto
        exit; // Detiene la ejecución del script después de la redirección
    }
}

// Limpiar el almacenamiento local si se cambió de proyecto
if ($idProyecto) {
    $storedProjectId = isset($_SESSION['projectId']) ? $_SESSION['projectId'] : null;
    if ($storedProjectId !== $idProyecto) {
        unset($_SESSION['selectedMaterials'][$idProyecto]); // Elimina la selección de materiales para el proyecto actual
    }
    $_SESSION['projectId'] = $idProyecto;
}

?>

<body>
    <div class="container">
        <h1>Lista de Materiales</h1>
        <!-- Formulario de búsqueda -->
        <form action="" method="POST" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" id="busqueda" name="busqueda" placeholder="Buscar materiales">
            </div>
        </form>

        <div class="d-flex justify-content-between">
            <!-- volver -->
            <a href="<?php echo isset($_GET['id']) ? 'visualizar.php?id=' . $_GET['id'] : 'visualizar.php'; ?>" class="btn btn-warning m-2">Volver</a>
      
        </div>

        <!-- Formulario para mostrar y seleccionar materiales -->
        <form action="" method="POST" class="d-flex flex-column">
            <!-- Input oculto para pasar el ID del proyecto -->
            <input type="hidden" name="id_inspeccion" value="<?php echo $idProyecto; ?>">
            <div class="mt-auto">
                <button type="submit" class="btn btn-primary" id="guardarSeleccion" onclick="limpiarLocalStorage()" disabled>Guardar Selección</button>
            </div>
            <div class="form-group mt-3">
                <!-- Contenedor para la lista de materiales -->
                <div id="listaMateriales">
                    <?php
                    // Verificar si se encontraron materiales
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Mostrar los materiales
                            echo '<div class="form-check">';
                            echo '<input class="form-check-input border-primary" type="checkbox" name="materiales[]" value="' . $row['codigo'] . '">';
                            echo '<label class="form-check-label">' . $row['nombre'] . '</label>';
                            echo '</div>';
                        }
                    } else {
                        // No se encontraron materiales
                        echo 'No se encontraron materiales.';
                    }
                    ?>
                </div>
            </div>
        </form>

        <script>
            function limpiarLocalStorage() {
                localStorage.removeItem('selectedMaterials_<?php echo $idProyecto; ?>');
            }
        </script>


    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Limpiar almacenamiento local al cargar la página
            limpiarLocalStorage();

            // Obtiene los materiales seleccionados almacenados en el local storage
            var selectedMaterials = JSON.parse(localStorage.getItem('selectedMaterials_' + <?php echo $idProyecto; ?>)) || [];

            // Recorre los materiales seleccionados y marca los checkbox correspondientes
            selectedMaterials.forEach(function(material) {
                var checkbox = document.querySelector('input[name="materiales[]"][value="' + material + '"]');
                if (checkbox) {
                    checkbox.checked = true;
                }
            });

            // Verificar si hay algún checkbox marcado al cargar la página
            verificarSeleccion();

            // Escucha los eventos de cambio en los checkbox
            var checkboxes = document.querySelectorAll('input[name="materiales[]"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function(event) {
                    // Si el checkbox está marcado y el material no está en la lista, agregarlo
                    if (this.checked && !selectedMaterials.includes(parseInt(this.value))) {
                        selectedMaterials.push(parseInt(this.value));
                    } else if (!this.checked) { // Si el checkbox está desmarcado, remover el material de la lista
                        var index = selectedMaterials.indexOf(parseInt(this.value));
                        if (index !== -1) {
                            selectedMaterials.splice(index, 1);
                        }
                    }

                    // Guarda los materiales seleccionados en el local storage
                    localStorage.setItem('selectedMaterials_' + <?php echo $idProyecto; ?>, JSON.stringify(selectedMaterials));

                    // Verificar si hay algún checkbox marcado al cambiar su estado
                    verificarSeleccion();
                });
            });

            // Función para verificar si hay algún checkbox marcado y habilitar/deshabilitar el botón "Guardar Selección"
            function verificarSeleccion() {
                var checkboxes = document.querySelectorAll('input[name="materiales[]"]');
                var botonGuardar = document.getElementById('guardarSeleccion');
                var algunSeleccionado = Array.from(checkboxes).some(function(checkbox) {
                    return checkbox.checked;
                });
                botonGuardar.disabled = !algunSeleccionado;
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var busquedaInput = document.getElementById('busqueda');
            var listaMateriales = document.getElementById('listaMateriales');

            busquedaInput.addEventListener('input', function() {
                var busqueda = this.value.toLowerCase();
                var materiales = listaMateriales.getElementsByClassName('form-check');

                for (var i = 0; i < materiales.length; i++) {
                    var label = materiales[i].getElementsByTagName('label')[0];
                    var texto = label.textContent.toLowerCase();

                    if (texto.indexOf(busqueda) !== -1) {
                        materiales[i].style.display = '';
                    } else {
                        materiales[i].style.display = 'none';
                    }
                }
            });
        });
    </script>
</body>

</html>
