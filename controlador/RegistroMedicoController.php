<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-01, PROBLEMA-07
 *   password_hash(bcrypt) aplicado antes de guardar la contraseña.
 */
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
include_once '../modelo/Medico.php';

require_once 'auth_middleware.php';
validateCsrf();

$medico  = new Medico();
$funcion = $_POST['funcion'] ?? '';

if ($funcion == 'crear_medico') {
    $nombre    = trim($_POST['nombre']    ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $cedula    = trim($_POST['cedula']    ?? '');
    $telefono  = trim($_POST['telefono']  ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $correo    = trim($_POST['correo']    ?? '');
    $sexo      = $_POST['sexo']           ?? '';
    $adicional = $_POST['adicional']      ?? '';
    $pass      = $_POST['pass']           ?? '';
    $tipo      = 2;
    $avatar    = 'avatarDES.jpg';

    $errores = [];
    if (empty($nombre))    $errores[] = 'Nombre requerido';
    if (empty($apellidos)) $errores[] = 'Apellidos requeridos';
    if (empty($cedula))    $errores[] = 'Cédula requerida';
    if (empty($pass))      $errores[] = 'Contraseña requerida';
    if (strlen($pass) < 8) $errores[] = 'La contraseña debe tener al menos 8 caracteres';

    if (!empty($errores)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errores)]);
        exit();
    }

    $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

    ob_start();
    $medico->crear($nombre, $apellidos, $fecha_nacimiento, $cedula,
                   $telefono, $direccion, $correo, $sexo, $adicional,
                   $hash, $tipo, $avatar);
    $resultado = trim(ob_get_clean());

    if ($resultado === 'add') {
        echo json_encode(['success' => true,  'message' => 'Cuenta de médico creada exitosamente']);
    } elseif ($resultado === 'existe') {
        echo json_encode(['success' => false, 'message' => 'Ya existe un médico con esta cédula o correo']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la cuenta']);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Función no válida']);
?>
