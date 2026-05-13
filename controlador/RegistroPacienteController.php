<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-01, PROBLEMA-07
 *   Se aplica password_hash(bcrypt) a la contraseña antes de guardarla.
 *   Se desactivan errores en pantalla (producción).
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-07
 *   error_reporting desactivado para producción.
 */
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
include_once '../modelo/Paciente.php';

require_once 'auth_middleware.php';
// Registro no requiere sesión previa, pero sí validar CSRF
validateCsrf();

$paciente = new Paciente();
$funcion  = $_POST['funcion'] ?? '';

if ($funcion == 'crear_paciente') {
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
    $tipo      = 1;
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

    // [SECURITY FIX] PROBLEMA-01: Hashear contraseña con bcrypt cost=12
    $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

    ob_start();
    $paciente->crear($nombre, $apellidos, $fecha_nacimiento, $cedula,
                     $telefono, $direccion, $correo, $sexo, $adicional,
                     $hash, $tipo, $avatar);
    $resultado = trim(ob_get_clean());

    if ($resultado === 'add') {
        echo json_encode(['success' => true,  'message' => 'Cuenta de paciente creada exitosamente']);
    } elseif ($resultado === 'existe') {
        echo json_encode(['success' => false, 'message' => 'Ya existe un paciente con esta cédula o correo']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la cuenta']);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Función no válida']);
?>
