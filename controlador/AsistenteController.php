<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-02, PROBLEMA-03, PROBLEMA-04, PROBLEMA-05
 */
error_reporting(0);
ini_set('display_errors', 0);

include_once '../modelo/Asistente.php';
require_once 'auth_middleware.php';

requireAuth('asistente');   // C4: Solo asistentes autenticados
validateCsrf();

header('Content-Type: application/json');

$asistente = new Asistente();
$funcion   = $_POST['funcion'] ?? '';

if ($funcion === 'buscar_asistente') {
    $fecha_actual = new DateTime();
    $asistente->obtener_datos($_POST['dato'] ?? '');
    if (empty($asistente->objetos)) {
        echo json_encode(['error' => 'No se encontró el asistente']);
        exit();
    }
    $objeto     = $asistente->objetos[0];
    $nacimiento = new DateTime($objeto->fecha_nacimiento_asistente);
    echo json_encode([
        'nombre'           => $objeto->nombre_asistente,
        'apellidos'        => $objeto->apellido_asistente,
        'fecha_nacimiento' => $nacimiento->diff($fecha_actual)->y,
        'cedula'           => $objeto->cedula_asistente,
        'tipo'             => $objeto->nombre_tipo_asistente,
        'telefono'         => $objeto->telefono_asistente,
        'direccion'        => $objeto->direccion_asistente,
        'correo'           => $objeto->correo_asistente,
        'sexo'             => $objeto->sexo_asistente,
        'adicional'        => $objeto->adicional_asistente,
        'avatar'           => '../../img/' . $objeto->avatar_asistente,
    ]);
    exit();
}

if ($funcion === 'capturar_datos') {
    $id_asistente = (int)($_POST['id_asistente'] ?? $_SESSION['usuario']);
    requireOwnership($id_asistente);

    $asistente->obtener_datos($id_asistente);
    if (empty($asistente->objetos)) {
        echo json_encode(['error' => 'No se encontró el asistente']);
        exit();
    }
    $objeto = $asistente->objetos[0];
    echo json_encode([
        'telefono'  => $objeto->telefono_asistente,
        'direccion' => $objeto->direccion_asistente,
        'correo'    => $objeto->correo_asistente,
        'sexo'      => $objeto->sexo_asistente,
        'adicional' => $objeto->adicional_asistente,
    ]);
    exit();
}

if ($funcion === 'editar_asistente') {
    $id_asistente = (int)($_POST['id_asistente'] ?? 0);
    requireOwnership($id_asistente);

    $asistente->editar(
        $id_asistente,
        trim($_POST['telefono']  ?? ''),
        trim($_POST['direccion'] ?? ''),
        trim($_POST['correo']    ?? ''),
        $_POST['sexo']      ?? '',
        $_POST['adicional'] ?? ''
    );
    echo json_encode(['success' => true, 'message' => 'editado']);
    exit();
}

if ($funcion === 'cambiar_foto') {
    $id_asistente = (int)$_SESSION['usuario'];

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
    $asistente->cambiar_photo($id_asistente, $nombre);

    if (!empty($asistente->objetos)) {
        foreach ($asistente->objetos as $objeto) {
            $anterior = $objeto->avatar_asistente ?? '';
            if ($anterior && $anterior !== 'avatarDES.jpg' && file_exists('../img/' . $anterior)) {
                unlink('../img/' . $anterior);
            }
        }
    }

    echo json_encode(['ruta' => $ruta, 'alert' => 'edit']);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Función no válida']);
?>
