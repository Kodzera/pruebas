<?php

$url = "https://tecniredinco.com/";
// $url = "http://localhost/lizg/";

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REDINCO</title>
    <!-- Agrega el enlace al archivo CSS de la fuente Onest -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Onest:wght@100..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpg" href="<?php echo $url ?>images/pesta.png" />

    <!-- Agrega el enlace al archivo CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <style>
      
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            /*overflow: hidden;*/
        }
        body {
            font-family: "Onest", sans-serif;
            background-color: #F4F6F1;
        }

        #descripcion {
            resize: none;
        }

        @media screen and (max-width: 768px) {

            /* Estilos para dispositivos con dimensiones similares a tabletas */
            .hide-on-tablet {
                display: none;
                /* Ocultar en tabletas */
            }

            .contenedor-botones {
                display: flex;
                flex-direction: column;
            }

            .servicios {
                padding: 10px;
                /* Reduce el espacio alrededor del contenido */
            }

            .imagen,
            .icono {
                height: 80px;
                /* Reducir el tamaño de las imágenes */
            }

            .servicio {
                height: 150px;
                /* Reducir el tamaño de los servicios */
                width: 150px;
            }

            .servicios_ {
                flex-direction: column;
                /* Cambiar a una disposición de columna */
                max-width: 300px;
                /* Reducir el ancho máximo */
                margin: auto;
                /* Centrar en la pantalla */
            }

            .izquierda,
            .derecha {
                text-align: center;
                /* Centrar el texto */
                margin: 10px 0;
                /* Añadir espacio entre los servicios */
            }

            .imagen{
                display: none;
            }

            .logo-redinco{
                max-width: 100%;
                overflow: hidden;
            }

            .table-responsive{
                padding: 10px;
            }
        }

        /* Estilos para dispositivos más grandes que las tabletas pero más pequeños que las laptops */
        @media screen and (min-width: 769px) and (max-width: 1024px) {
            .hide-on-laptop {
                display: none;
                /* Ocultar en laptops */
            }

            .contenedor-botones {
                display: flex;
                flex-direction: column;
            }
            .imagen{
                display: none;
            }
        }
    </style>

</head>


<body class="">