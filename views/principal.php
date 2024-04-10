<?php
// Incluir el archivo de configuración de la base de datos
include "../config/database.php";

// Incluir el encabezado
include "../config/partials/header.php";

session_start();

// Verificar la sesión del usuario
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit(); // Asegurar que se detenga la ejecución después de redireccionar
}

?>
<style>
    body {
        margin: 0;

    }

    .servicios {
        height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: white;
        padding: 20px;
        position: relative;
    }

    .imagen {
        position: absolute;
        top: 20px;
        left: 20px;
        height: 100px;
    }

    .icono {
        position: absolute;
        top: 20px;
        right: 20px;
        height: 30px;
    }

    .servicios_ {
        display: flex;
        justify-content: space-evenly;
        align-items: center;
        max-width: 800px;
        width: 100%;
    }

    .servicio-blue {
        background-color: #1A3372;
        transition: background-color 0.3s ease-in-out;
    }

    .servicio-celeste {
        background-color: #6DCAD8;
        transition: background-color 0.3s ease-in-out;
    }

    .servicio {
        height: 200px;
        width: 200px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 20px 0;
        position: relative;
    }

    .servicio img {
        max-width: 100%;
        max-height: 100%;
        transition: transform 0.3s ease-in-out;
    }

    .servicio:hover img {
        transform: scale(1.1);
    }

    .izquierda,
    .derecha {
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        flex: 1;
    }

    h4 {
        color: #000000;
        text-transform: uppercase;
        margin-top: 10px;
        border-bottom: 3px solid transparent;
        border-image: linear-gradient(0.25turn, #6dcad8, #1a3372);
        border-image-slice: 1;
        width: 90%;
    }

    @media (max-width: 767px) {
        .servicios_ {
            flex-direction: column;
            align-items: center;
        }
    }
</style>
</head>

<body>

    <div class="servicios">
        <img src="../images/encabezadoactual.png" alt="LOGO DE REDINCO" class="imagen">
        <a href="#" data-bs-toggle="modal" data-bs-target="#confirmarLogoutModal">
            <img src="../images/icono.png" alt="" class="icono" title="Cerrar Sesión">
        </a>

        <div class="servicios_">
            <div class="izquierda">
                <h4 style="margin-bottom: 20px;">Inspección</h4>
                <div class="servicio servicio-blue">
                    <a href="inspeccion/index.php">
                        <img src="../images/HeroiconsDocumentCheck20Solid.webp" alt="Inspección" class="inspeccion" title="INSPECCIÓN">
                    </a>
                </div>
            </div>
            <div class="derecha">
                <h4>Solicitud de Materiales</h4>
                <div class="servicio servicio-celeste">
                    <a href="home.php">
                        <img src="../images/TablerTools.webp" alt="Solicitud de Materiales" class="materiales" title="SOLICITUD DE MATERIALES">
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para cerrar sesión -->
    <div class="modal fade" id="confirmarLogoutModal" tabindex="-1" aria-labelledby="confirmarLogoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm"> <!-- Agrega la clase modal-sm -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmarLogoutModalLabel">Confirmar Cierre de Sesión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que quieres cerrar sesión?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="logout.php" class="btn btn-primary">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>
    <!-- JavaScript para activar el modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = new bootstrap.Modal(document.getElementById('confirmarLogoutModal'), {
                keyboard: false // Evita que se cierre con la tecla "Esc"
            });

            // Abre el modal cuando se haga clic en el icono de cerrar sesión
            document.querySelector('.icono').addEventListener('click', function() {
                myModal.show();
            });
        });
    </script>
</body>

</html>