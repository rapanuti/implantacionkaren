<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-01, PROBLEMA-08, PROBLEMA-15
 *   B9: session_regenerate_id(true) tras autenticación exitosa (previene session fixation).
 *   Se eliminaron los error_log() con datos sensibles de usuarios.
 *   El middleware ya inicia la sesión con parámetros seguros.
 */
error_reporting(0);
ini_set('display_errors', 0);

require_once 'auth_middleware.php';  // Inicia sesión segura + define h(), requireAuth(), etc.

include_once '../modelo/LoginPaciente.php';
include_once '../modelo/LoginMedico.php';
include_once '../modelo/LoginAsistente.php';
include_once '../modelo/LoginAdministrador.php';

$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$user = trim($_POST['user'] ?? '');
$pass = $_POST['pass'] ?? '';
$rol  = $_POST['rol']  ?? '';

// Si ya hay sesión activa, redirigir
if (!empty($_SESSION['us_tipo']) && !empty($_SESSION['rol'])) {
    $redirect = _biovital_redirect_por_rol($_SESSION['rol']);
    if ($is_ajax) {
        echo json_encode(['success' => true, 'redirect' => $redirect]);
    } else {
        header('Location: ' . $redirect);
    }
    exit();
}

// ── Procesar login según rol ────────────────────────────────
$login_exitoso = false;
$rol_usuario   = '';

$roles_validos = ['paciente', 'medico', 'asistente', 'administrador'];
if (!in_array($rol, $roles_validos, true)) {
    _biovital_login_fallido($is_ajax);
    exit();
}

switch ($rol) {
    case 'paciente':
        $login  = new LoginPaciente();
        $result = $login->Loguearse($user, $pass);
        if (!empty($result)) {
            $obj = $result[0];
            // [SECURITY FIX] PROBLEMA-08: Regenerar ID de sesión tras login exitoso
            session_regenerate_id(true);
            $_SESSION['usuario']   = $obj->id_paciente;
            $_SESSION['us_tipo']   = $obj->paciente_tipo;
            $_SESSION['nombre_us'] = $obj->nombre_paciente;
            $_SESSION['rol']       = 'paciente';
            $login->actualizarUltimoAcceso($obj->id_paciente);
            $login_exitoso = true;
            $rol_usuario   = 'paciente';
        }
        break;

    case 'medico':
        $login  = new LoginMedico();
        $result = $login->Loguearse($user, $pass);
        if (!empty($result)) {
            $obj = $result[0];
            session_regenerate_id(true);
            $_SESSION['usuario']   = $obj->id_medico;
            $_SESSION['us_tipo']   = $obj->medico_tipo;
            $_SESSION['nombre_us'] = $obj->nombre_medico;
            $_SESSION['rol']       = 'medico';
            $login->actualizarUltimoAcceso($obj->id_medico);
            $login_exitoso = true;
            $rol_usuario   = 'medico';
        }
        break;

    case 'asistente':
        $login  = new LoginAsistente();
        $result = $login->Loguearse($user, $pass);
        if (!empty($result)) {
            $obj = $result[0];
            session_regenerate_id(true);
            $_SESSION['usuario']   = $obj->id_asistente;
            $_SESSION['us_tipo']   = $obj->asistente_tipo;
            $_SESSION['nombre_us'] = $obj->nombre_asistente;
            $_SESSION['rol']       = 'asistente';
            $login->actualizarUltimoAcceso($obj->id_asistente);
            $login_exitoso = true;
            $rol_usuario   = 'asistente';
        }
        break;

    case 'administrador':
        $login  = new LoginAdministrador();
        $result = $login->Loguearse($user, $pass);
        if (!empty($result)) {
            $obj = $result[0];
            session_regenerate_id(true);
            $_SESSION['usuario']   = $obj->id_administrador;
            $_SESSION['us_tipo']   = $obj->administrador_tipo;
            $_SESSION['nombre_us'] = $obj->nombre_administrador;
            $_SESSION['rol']       = 'administrador';
            $login->actualizarUltimoAcceso($obj->id_administrador);
            $login_exitoso = true;
            $rol_usuario   = 'administrador';
        }
        break;
}

// ── Responder ───────────────────────────────────────────────
if ($login_exitoso) {
    $redirect = _biovital_redirect_por_rol($rol_usuario);
    if ($is_ajax) {
        echo json_encode(['success' => true, 'redirect' => $redirect]);
    } else {
        header('Location: ' . $redirect);
    }
} else {
    _biovital_login_fallido($is_ajax);
}

// ── Helpers locales ─────────────────────────────────────────
function _biovital_redirect_por_rol(string $rol): string {
    $mapa = [
        'paciente'      => '../vista/paciente/pac_catalogo.php',
        'medico'        => '../vista/medico/med_catalogo.php',
        'asistente'     => '../vista/asistente/asi_catalogo.php',
        'administrador' => '../vista/administrador/adm_catalogo.php',
    ];
    return $mapa[$rol] ?? '../index.php';
}

function _biovital_login_fallido(bool $is_ajax): void {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'error' => 'Cédula o contraseña incorrecta']);
    } else {
        header('Location: ../index.php?error=1');
    }
}
?>
