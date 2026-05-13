<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-02, PROBLEMA-03, PROBLEMA-04, PROBLEMA-05, PROBLEMA-15
 */
error_reporting(0);
ini_set('display_errors', 0);

include_once '../modelo/Medico.php';
require_once 'auth_middleware.php';

requireAuth('medico');   // C1: Solo médicos autenticados
validateCsrf();           // D4: Validar CSRF

header('Content-Type: application/json');

$medico  = new Medico();
$funcion = $_POST['funcion'] ?? '';

// ── Buscar médico ─────────────────────────────────────────────────────────
if ($funcion === 'buscar_medico') {
    $fecha_actual = new DateTime();
    $medico->obtener_datos($_POST['dato'] ?? '');
    if (empty($medico->objetos)) {
        echo json_encode(['error' => 'No se encontró el médico']);
        exit();
    }
    $objeto     = $medico->objetos[0];
    $nacimiento = new DateTime($objeto->fecha_nacimiento_medico);
    $avatar     = (!empty($objeto->avatar_medico) && $objeto->avatar_medico !== 'avatarDES.jpg')
                  ? '../../img/' . $objeto->avatar_medico
                  : '../../img/avatarDES.jpg';
    echo json_encode([
        'nombre'           => $objeto->nombre_medico,
        'apellidos'        => $objeto->apellido_medico,
        'fecha_nacimiento' => $nacimiento->diff($fecha_actual)->y,
        'cedula'           => $objeto->cedula_medico,
        'tipo'             => $objeto->nombre_tipo,
        'telefono'         => $objeto->telefono_medico,
        'direccion'        => $objeto->direccion_medico,
        'correo'           => $objeto->correo_medico,
        'sexo'             => $objeto->sexo_medico,
        'adicional'        => $objeto->adicional_medico,
        'avatar'           => $avatar,
    ]);
    exit();
}

// ── Capturar datos propios ────────────────────────────────────────────────
if ($funcion === 'capturar_datos') {
    $id_medico = (int)($_POST['id_medico'] ?? $_SESSION['usuario']);
    requireOwnership($id_medico);  // C6: Solo puede ver sus propios datos

    $medico->obtener_datos($id_medico);
    if (empty($medico->objetos)) {
        echo json_encode(['error' => 'No se encontró el médico']);
        exit();
    }
    $objeto = $medico->objetos[0];
    echo json_encode([
        'telefono'  => $objeto->telefono_medico,
        'direccion' => $objeto->direccion_medico,
        'correo'    => $objeto->correo_medico,
        'sexo'      => $objeto->sexo_medico,
        'adicional' => $objeto->adicional_medico,
    ]);
    exit();
}

// ── Editar médico ─────────────────────────────────────────────────────────
if ($funcion === 'editar_medico') {
    $id_medico = (int)($_POST['id_medico'] ?? 0);
    requireOwnership($id_medico);  // C6: IDOR fix

    $medico->editar(
        $id_medico,
        trim($_POST['telefono']  ?? ''),
        trim($_POST['direccion'] ?? ''),
        trim($_POST['correo']    ?? ''),
        $_POST['sexo']      ?? '',
        $_POST['adicional'] ?? ''
    );
    echo json_encode(['success' => true, 'message' => 'editado']);
    exit();
}

// ── Cambiar foto ──────────────────────────────────────────────────────────
if ($funcion === 'cambiar_foto') {
    $id_medico = (int)$_SESSION['usuario'];  // Siempre desde sesión

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['alert' => 'noedit', 'message' => 'Error en la subida']);
        exit();
    }
    if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
        echo json_encode(['alert' => 'noedit', 'message' => 'Archivo mayor a 5 MB']);
        exit();
    }

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeReal = $finfo->file($_FILES['photo']['tmp_name']);
    $mimeMap  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (!array_key_exists($mimeReal, $mimeMap)) {
        echo json_encode(['alert' => 'noedit', 'message' => 'Tipo no permitido']);
        exit();
    }

    $nombre = bin2hex(random_bytes(16)) . '.' . $mimeMap[$mimeReal];
    $ruta   = '../img/' . $nombre;
    move_uploaded_file($_FILES['photo']['tmp_name'], $ruta);
    $medico->cambiar_photo($id_medico, $nombre);

    if (!empty($medico->objetos)) {
        foreach ($medico->objetos as $objeto) {
            $anterior = $objeto->avatar_medico ?? '';
            if ($anterior && $anterior !== 'avatarDES.jpg' && file_exists('../img/' . $anterior)) {
                unlink('../img/' . $anterior);
            }
        }
    }

    echo json_encode(['ruta' => $ruta, 'alert' => 'edit']);
    exit();
}

// ── Estadísticas ──────────────────────────────────────────────────────────
if ($funcion === 'mis_estadisticas') {
    $id_medico = (int)$_SESSION['usuario'];
    echo json_encode([
        'total_recetas'   => $medico->contarRecetas($id_medico),
        'total_pacientes' => $medico->contarPacientes($id_medico),
    ]);
    exit();
}

// ── Listar pacientes del médico ───────────────────────────────────────────
if ($funcion === 'listar_pacientes') {
    $id_medico = (int)$_SESSION['usuario'];
    echo json_encode($medico->listarPacientes($id_medico));
    exit();
}

echo json_encode(['success' => false, 'message' => 'Función no válida']);
?>
