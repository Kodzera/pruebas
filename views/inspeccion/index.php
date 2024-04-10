<?php
// Incluir archivo de configuración de la base de datos
include "../../config/database.php";
// Incluir el encabezado
include "../../config/partials/header.php";
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

// Definir la consulta SQL base
$sql = "SELECT i.id_inspeccion, i.fecha AS fecha, i.nombre_inspeccion, u.nombre AS nombre_tecnico, i.aprobado_inspeccion
        FROM inspeccion i 
        INNER JOIN usuarios u ON i.usuario_id = u.id
        WHERE i.aprobado_inspeccion = 0"; // Mostrar solo las inspecciones que no han sido aprobadas

// Si el usuario es un administrador, seleccionar todas las inspecciones
if ($rol === 'admin') {
    $sql .= " ORDER BY i.id_inspeccion DESC";
    $stmt = $conn->prepare($sql);
} else {
    // Si el usuario es un usuario normal, seleccionar solo las inspecciones asociadas a su ID de usuario
    $sql .= " AND i.usuario_id = ? 
              ORDER BY i.id_inspeccion DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idUsuario);
}

$stmt->execute();
$result = $stmt->get_result();

// Verificar si se envió la solicitud de aprobación
if (isset($_POST['id_inspeccion']) && isset($_POST['aprobar'])) {
    // Obtener el ID de la inspección y el nuevo valor de aprobación
    $idInspeccion = $_POST['id_inspeccion'];
    $aprobar = $_POST['aprobar'];

    // Actualizar el estado de aprobación en la base de datos
    $updateSql = "UPDATE inspeccion SET aprobado_inspeccion = ? WHERE id_inspeccion = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ii", $aprobar, $idInspeccion);
    $updateStmt->execute();

    // Redirigir de nuevo a esta página para actualizar la tabla
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>


<body class="d-flex flex-column h-100">
    <style>
        .aprobada {
            background-color: #d4edda !important;
            /* Color verde claro */
        }
    </style>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <!-- Botón "Volver" a la izquierda -->
            <a href="../principal.php" class="navbar-brand btn btn-warning me-auto">
                <i class="bi bi-arrow-left"></i> Volver
            </a>

            <!-- Botón "Crear Inspección" a la derecha -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <style>
                        .crear{
                            background-color: #6DCAD8;
                            color: #000000;
                        }
                    </style>
                    <a href="./crud/crear.php" class="nav-link btn btn-primary me-2 crear mt-2 p-2">Crear Inspección</a>
                </li>
            </ul>
        </div>
    </nav>


    <main class="container py-4">
        <input type="text" id="searchInput" class="form-control mb-2" placeholder="Buscar por proyecto, técnico o fecha" autofocus>
        <!-- MOSTRAR ALERTAS DE CREACION -->
        <?php
        if (isset($_SESSION['msg']) && isset($_SESSION['color'])) {
            echo "<div class='alert alert-{$_SESSION['color']} alert-dismissible fade show text-center' role='alert'>
                    {$_SESSION['msg']}
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
            unset($_SESSION['color']);
            unset($_SESSION['msg']);
        }
        ?>
        <div class="table-responsive">
            <table id="projectTable" class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center hide-on-mobile">#</th>
                        <th class="text-center hide-on-tablet">Fecha</th>
                        <th class="text-center">Inspeccion</th>
                        <th class="text-center hide-on-mobile hide-on-tablet">Técnico</th>
                        <th class="text-center">Acción</th>
                        <th class="text-center">Aprobar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Determinar la clase CSS basada en el valor de aprobado_inspeccion
                            $aprobadoClass = $row['aprobado_inspeccion'] == 1 ? 'aprobada' : '';
                            echo "<tr class='$aprobadoClass'>";
                            echo "<td class='text-center hide-on-mobile'>" . $row['id_inspeccion'] . "</td>";
                            echo "<td class='text-center projectDate hide-on-tablet'>" . $row['fecha'] . "</td>";
                            echo "<td class='text-center projectName'>" . $row['nombre_inspeccion'] . "</td>";
                            echo "<td class='text-center hide-on-mobile technicianName hide-on-tablet'>" . $row['nombre_tecnico'] . "</td>";
                            echo "<td class='text-center'><a href='./crud/visualizar.php?id=" . $row['id_inspeccion'] . "' class='btn btn-success btn-sm'>Visualizar</a></td>";
                            // Botón para aprobar
                            echo "<td class='text-center'>
                                <button class='btn btn-primary btn-sm btn-aprobar' data-id='{$row['id_inspeccion']}'>Aprobar</button>
                                <!-- Agregar campo oculto para almacenar el id_inspeccion -->
                                <input type='hidden' class='id-inspeccion' value='{$row['id_inspeccion']}'>
                            </td>";
                            echo "</tr>";
                        }
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
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-aprobar').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idInspeccion = this.getAttribute('data-id');
                    aprobarInspeccion(idInspeccion);
                });
            });

            function aprobarInspeccion(idInspeccion) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'aprobar.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        location.reload();
                    } else {
                        console.error('Error al aprobar la inspección');
                    }
                };
                xhr.send(`id_inspeccion=${idInspeccion}`);
            }

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
        });
    </script>
</body>

</html>