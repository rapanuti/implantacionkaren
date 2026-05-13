<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap"rel="stylesheet">
    <link rel="stylesheet" type="text/css " href="css/style.css">
    <link rel="stylesheet" type="text/css " href="css/css/all.min.css">
</head>
<?php
session_start();
if(!empty($_SESSION['us_tipo'])){
    header('Location: controlador/LoginController.php');
}
else{ //****esta llave cierra al final del codigo */
session_destroy();
?>
<body>
    <img class="wave"src="img/wave.png" alt="">
    <div class="contenedor">
        <div class="img">
            <img src="img/bg.svg" alt="">
        </div>
        <div class="contenido-login">
            <form action="controlador/LoginController.php" method="POST">            
                <img src="img/logo_azul.png" alt="">
                <h2>Biovital</h2>
              
            
               <div class="text-center mt-3">
                  <a href="vista/login_paciente.php" class="btn btn-link" style="text-decoration: none;">Paciente</a>
                </div>
                <div class="text-center mt-3">
                   <a href="vista/login_medico.php" class="btn btn-link" style="text-decoration: none;">Medico</a>
                </div>
                <div class="text-center mt-3">
                  <a href="vista/login_asistente.php" class="btn btn-link" style="text-decoration: none;">Asistente</a>
                </div>
                <div class="text-center mt-3">
                  <a href="vista/login_administrador.php" class="btn btn-link" style="text-decoration: none;">Administrador</a>
                </div>
              
            </form>
        </div>
    </div>

</body>
<script src="js/login.js"></script>
</html>
<?php
}
?>
