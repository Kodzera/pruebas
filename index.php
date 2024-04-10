<?php
session_start();
require "config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Consulta SQL para seleccionar el usuario por su nombre de usuario
    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_password = $row['contrasena'];

        // Verificar la contraseña
        if ($password === $stored_password) {
            $_SESSION['id_usuario'] = $row['id'];
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['rol'] = $row['rol']; // Agregar el rol del usuario a la sesión
            header("Location: views/principal.php");
            exit();
        } else {
            $error_msg = "La contraseña no coincide";
        }
    } else {
        $error_msg = "Usuario no encontrado";
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redinco</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Agrega el enlace al archivo CSS de la fuente Onest -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Onest:wght@100..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpg" href="./images/pesta.png" />

    <style>
        body {
            background-image: url('images/fondo-negro.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            font-family: "Onest", sans-serif;

        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 350px;
            max-width: 100%;
            margin: auto;
            animation: fadeInDown 1s ease;
        }

        .card-img-top {
            width: 50%;
            margin: auto;
            padding-top: 20px;
        }

        .card-header {
            background-color: transparent;
            border-bottom: none;
            text-align: center;
            padding-bottom: 0;
        }

        .card-body {
            padding: 40px;
        }

        .btn-primary {
            background-color: #1C3969;
            border-color: #1C3969;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #143257;
            border-color: #143257;
        }

        .form-control,
        .btn {
            border-radius: 20px;
        }

        .materiales{
            font-weight: 700;
        }

        @keyframes fadeInDown {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <img class="card-img-top" src="images/logo.png" alt="Logo">
                    <div class="card-header">
                        <h3 class="font-weight-light">Aplicativo de <span class="materiales" style="color: #1C3969;">Materiales</span></h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <div class="form-group">
                                <input class="form-control py-4" id="inputUsuario" name="usuario" type="text" placeholder="Usuario" autofocus required>
                            </div>
                            <div class="form-group">
                                <input class="form-control py-4" id="inputPassword" name="password" type="password" placeholder="Contraseña" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                            </div>
                            <?php if (isset($error_msg)) : ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo $error_msg; ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>