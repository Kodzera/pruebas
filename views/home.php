<?php
// Incluir archivo de configuración de la base de datos
include "../config/database.php";
// Incluir el encabezado
include "../config/partials/header.php";
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit; // Detener la ejecución del script
}

// Obtener datos del usuario de la sesión
$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];
$idUsuario = $_SESSION['id_usuario'];

// Realizar la consulta a la tabla proyecto_aceptado
$sql_proyecto_aceptado = "SELECT pa.id_inspeccion, i.fecha, i.nombre_inspeccion, u.nombre as nombre_tecnico 
                          FROM proyecto_aceptado pa 
                          JOIN inspeccion i ON pa.id_inspeccion = i.id_inspeccion 
                          JOIN usuarios u ON i.usuario_id = u.id ORDER BY id_inspeccion DESC";

$stmt_proyecto_aceptado = $conn->prepare($sql_proyecto_aceptado);
$stmt_proyecto_aceptado->execute();
$result_proyecto_aceptado = $stmt_proyecto_aceptado->get_result();

?>


<body class="d-flex flex-column h-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <!-- Botón "Volver" a la izquierda -->
            <a href="principal.php" class="navbar-brand btn btn-warning me-auto">
                <i class="bi bi-arrow-left"></i> Volver
            </a>

            <!-- Botón "Crear Inspección" a la derecha -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <style>
                        .crear{
                            background-color: #1A3372;
                            color: #ffffff;
                        }
                    </style>
                    <a href="addproyecto.php" class="nav-link btn btn-primary me-2 crear mt-2 p-2">Crear Proyecto</a>
                </li>
            </ul>
        </div>
    </nav>




    <main class="container py-4">
        <input type="text" id="searchInput" class="form-control mb-2" placeholder="Buscar por proyecto, técnico o fecha" autofocus>



        <?php
        if (isset($_SESSION['msg']) && isset($_SESSION['color'])) {
            echo "<div class='alert alert-{$_SESSION['color']} alert-dismissible fade show text-center' role='alert'>
                    {$_SESSION['msg']}
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
            unset($_SESSION['color']);
            unset($_SESSION['msg']);
        }

        if (!empty($mensaje) && !empty($tipo)) {
            echo "<div class='alert alert-$tipo alert-dismissible fade show text-center' role='alert'>
                    $mensaje
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
        }
        ?>
        <div class="table-responsive">
            <table id="projectTable" class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Proyecto</th>
                        <th class="text-center hide-on-mobile hide-on-tablet">Técnico</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Crear un array para almacenar los IDs de los proyectos ya mostrados
                    $proyectos_mostrados = array();

                    if ($result_proyecto_aceptado->num_rows > 0) {
                        while ($row_aceptado = $result_proyecto_aceptado->fetch_assoc()) {
                            // Verificar si el ID del proyecto ya ha sido mostrado
                            if (!in_array($row_aceptado['id_inspeccion'], $proyectos_mostrados)) {
                                // Si el ID no ha sido mostrado, agregarlo al array de proyectos mostrados
                                $proyectos_mostrados[] = $row_aceptado['id_inspeccion'];

                                echo "<tr>";
                                echo "<td class='text-center'>" . $row_aceptado['id_inspeccion'] . "</td>";
                                echo "<td class='text-center projectDate'>" . $row_aceptado['fecha'] . "</td>";
                                echo "<td class='text-center projectName'>" . $row_aceptado['nombre_inspeccion'] . "</td>";
                                echo "<td class='text-center technicianName hide-on-mobile hide-on-tablet'>" . $row_aceptado['nombre_tecnico'] . "</td>";


                                echo "<td class='text-center'>";
                                echo "<a href='informacion_proyecto.php?id=" . $row_aceptado['id_inspeccion'] . "' class='btn btn-info'>Visualizar</a>";
                                echo "</td>";

                                echo "</tr>";
                            }
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No hay proyectos aceptados disponibles.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">©Redinco
                <?php echo date("Y"); ?> Todos los derechos reservados
            </span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cerrar el menú al hacer clic en un enlace del menú en dispositivos móviles
        document.querySelectorAll('.navbar-nav>li>a').forEach(function(elem) {
            elem.addEventListener('click', function() {
                document.querySelector('.navbar-collapse').classList.remove('show');
            });
        });
        // Función para realizar la búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function() {
            let searchTerm = this.value.trim(); // Obtener el valor del input y eliminar espacios en blanco al principio y al final
            // Filtrar los proyectos según el término de búsqueda
            filterProjects(searchTerm);
        });
        // Función para filtrar los proyectos según el término de búsqueda
        function filterProjects(searchTerm) {
            let rows = document.querySelectorAll('#projectTable tbody tr');
            rows.forEach(function(row) {
                let projectName = row.querySelector('.projectName').textContent.trim().toLowerCase();
                let technicianName = row.querySelector('.technicianName').textContent.trim().toLowerCase();
                let date = row.querySelector('.projectDate').textContent.trim();
                if (projectName.includes(searchTerm.toLowerCase()) || technicianName.includes(searchTerm.toLowerCase()) || date.includes(searchTerm)) {
                    row.style.display = ''; // Mostrar la fila si el término de búsqueda coincide con el nombre del proyecto, del técnico o la fecha
                } else {
                    row.style.display = 'none'; // Ocultar la fila si el término de búsqueda no coincide con el nombre del proyecto, del técnico o la fecha
                }
            });
        }
    </script>
</body>

</html>