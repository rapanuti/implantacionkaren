<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador - BioVital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" type="text/css" href="../css/css/all.min.css">
</head>
<?php
session_start();
// [SECURITY FIX] 2026-05-13 - PROBLEMA-04: Generar token CSRF para el formulario
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
if(!empty($_SESSION['us_tipo']) && $_SESSION['rol'] == 'administrador'){
    header('Location: administrador/adm_catalogo.php');
}
session_destroy();
?>
<body>
    <img class="wave" src="../img/wave.png" alt="">
    <div class="contenedor">
        <div class="img">
            <img src="../img/Administrador.svg" alt="">
        </div>
        <div class="contenido-login">
            <form id="form-login" method="POST">
                <img src="../img/logo_azul.png" alt="">
                <h2>Administrador</h2>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="rol" value="administrador">
                <div class="input-div cedula">
                    <div class="i">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="div">
                        <h5>Cédula</h5>
                        <input type="text" name="user" class="input" required>
                    </div>
                </div>
                <div class="input-div pass">
                    <div class="i">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="div">
                        <h5>Contraseña</h5>
                        <input type="password" name="pass" class="input" required>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="registro_administrador.php" class="btn btn-link" style="text-decoration: none;">Crear nueva cuenta</a>
                </div>
                <input type="submit" class="btn" value="Iniciar Sesión">
            </form>
        </div>
    </div>
</body>
<script src="../js/login.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(document).ready(function() {
    $('#form-login').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '../controlador/LoginController.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    window.location.href = response.redirect;
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function(xhr) {
                alert('Error de conexión');
            }
        });
    });
});
</script>
</html>