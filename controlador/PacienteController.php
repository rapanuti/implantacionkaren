<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-02: requireAuth() verifica sesión activa.
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-03: requireOwnership() previene IDOR en edición.
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-04: validateCsrf() en todas las peticiones POST.
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-05: finfo_file() en subida de archivos.
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-15: Eliminados error_log() con datos sensibles.
 */
error_reporting(0);
ini_set('display_errors', 0);

include_once '../modelo/Paciente.php';
require_once 'auth_middleware.php';

// C1: Verificar sesión activa (cualquier rol puede llamar a este controlador)
requireAuth();
// D4: Validar CSRF en todas las peticiones POST
validateCsrf();

header('Content-Type: application/json');

$paciente = new Paciente();
$funcion  = $_POST['funcion'] ?? '';

// ── Buscar paciente (médicos y asistentes buscan pacientes) ─────────────────
if ($funcion === 'buscar_paciente') {
    // Solo médicos, asistentes y administradores pueden buscar pacientes
    if (!in_array($_SESSION['rol'], ['medico', 'asistente', 'administrador'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Sin permisos para buscar pacientes']);
        exit();
    }
    $fecha_actual = new DateTime();
    $paciente->obtener_datos($_POST['dato'] ?? '');
    if (empty($paciente->objetos)) {
        echo json_encode(['error' => 'No se encontró el paciente']);
        exit();
    }
    $objeto = $paciente->objetos[0];
    $nacimiento = new DateTime($objeto->fecha_nacimiento_pac);
    echo json_encode([
        'nombre'           => $objeto->nombre_paciente,
        'apellidos'        => $objeto->apellido_paciente,
        'fecha_nacimiento' => $nacimiento->diff($fecha_actual)->y,
        'cedula'           => $objeto->cedula_paciente,
        'tipo'             => $objeto->nombre_tipo,
        'telefono'         => $objeto->telefono_paciente,
        'direccion'        => $objeto->direccion_paciente,
        'correo'           => $objeto->correo_paciente,
        'sexo'             => $objeto->sexo_paciente,
        'adicional'        => $objeto->adicional_paciente,
        'avatar'           => '../../img/' . $objeto->avatar_paciente,
    ]);
    exit();
}

// ── Capturar datos propios para editar ────────────────────────────────────
if ($funcion === 'capturar_datos') {
    // [SECURITY FIX] PROBLEMA-03: El paciente solo puede ver sus propios datos
    $id_paciente = (int)($_POST['id_paciente'] ?? $_SESSION['usuario']);
    requireOwnership($id_paciente);

    $paciente->obtener_datos($id_paciente);
    if (empty($paciente->objetos)) {
        echo json_encode(['error' => 'No se encontró el paciente']);
        exit();
    }
    $objeto = $paciente->objetos[0];
    echo json_encode([
        'telefono'  => $objeto->telefono_paciente,
        'direccion' => $objeto->direccion_paciente,
        'correo'    => $objeto->correo_paciente,
        'sexo'      => $objeto->sexo_paciente,
        'adicional' => $objeto->adicional_paciente,
    ]);
    exit();
}

// ── Editar paciente ────────────────────────────────────────────────────────
if ($funcion === 'editar_paciente') {
    // [SECURITY FIX] PROBLEMA-03: Verificar que solo edita sus propios datos
    $id_paciente = (int)($_POST['id_paciente'] ?? 0);
    requireOwnership($id_paciente);

    $telefono  = trim($_POST['telefono']  ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $correo    = trim($_POST['correo']    ?? '');
    $sexo      = $_POST['sexo']           ?? '';
    $adicional = $_POST['adicional']      ?? '';

    $paciente->editar($id_paciente, $telefono, $direccion, $correo, $sexo, $adicional);
    echo json_encode(['success' => true, 'message' => 'editado']);
    exit();
}

// ── Cambiar foto ───────────────────────────────────────────────────────────
if ($funcion === 'cambiar_foto') {
    // Siempre usar el ID de la sesión — ignorar cualquier ID del POST
    $id_paciente = (int)$_SESSION['usuario'];

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['alert' => 'noedit', 'message' => 'Error en la subida del archivo']);
        exit();
    }

    // [SECURITY FIX] PROBLEMA-05: Límite de tamaño (5 MB)
    if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
        echo json_encode(['alert' => 'noedit', 'message' => 'El archivo excede 5 MB']);
        exit();
    }

    // [SECURITY FIX] PROBLEMA-05: Validar MIME real con finfo (no el del cliente)
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeReal = $finfo->file($_FILES['photo']['tmp_name']);
    $mimeMap  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (!array_key_exists($mimeReal, $mimeMap)) {
        echo json_encode(['alert' => 'noedit', 'message' => 'Tipo de archivo no permitido']);
        exit();
    }

    // [SECURITY FIX] PROBLEMA-05: Nombre completamente regenerado (sin nombre del cliente)
    $extension = $mimeMap[$mimeReal];
    $nombre    = bin2hex(random_bytes(16)) . '.' . $extension;
    $ruta      = '../img/' . $nombre;

    move_uploaded_file($_FILES['photo']['tmp_name'], $ruta);
    $paciente->cambiar_photo($id_paciente, $nombre);

    // Eliminar avatar anterior si no es el default
    if (!empty($paciente->objetos)) {
        foreach ($paciente->objetos as $objeto) {
            $avatarAnterior = $objeto->avatar_paciente ?? '';
            if ($avatarAnterior && $avatarAnterior !== 'avatarDES.jpg') {
                $rutaAnterior = '../img/' . $avatarAnterior;
                if (file_exists($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }
        }
    }

    echo json_encode(['ruta' => $ruta, 'alert' => 'edit']);
    exit();
}

// ── Mis estadísticas ───────────────────────────────────────────────────────
if ($funcion === 'mis_estadisticas') {
    // [SECURITY FIX] PROBLEMA-03: Usar siempre el ID de la sesión
    $id_paciente = (int)$_SESSION['usuario'];

    // [SECURITY FIX] PROBLEMA-15: Eliminados error_log() con datos sensibles
    $total_recetas  = $paciente->contarRecetas($id_paciente);
    $proximas_citas = $paciente->contarProximasCitas($id_paciente);

    echo json_encode([
        'total_recetas'  => $total_recetas,
        'proximas_citas' => $proximas_citas,
    ]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Función no válida']);
?>
