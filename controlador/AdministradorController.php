<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-02, PROBLEMA-03, PROBLEMA-04, PROBLEMA-05
 */
error_reporting(0);
ini_set('display_errors', 0);

include_once '../modelo/Administrador.php';
require_once 'auth_middleware.php';

requireAuth('administrador');   // C3: Solo administradores autenticados
validateCsrf();

header('Content-Type: application/json');

$administrador = new Administrador();
$funcion       = $_POST['funcion'] ?? '';

if ($funcion === 'buscar_administrador') {
    $fecha_actual = new DateTime();
    $administrador->obtener_datos($_POST['dato'] ?? '');
    if (empty($administrador->objetos)) {
        echo json_encode(['error' => 'No se encontró el administrador']);
        exit();
    }
    $objeto     = $administrador->objetos[0];
    $nacimiento = new DateTime($objeto->fecha_nacimiento_administrador);
    echo json_encode([
        'nombre'           => $objeto->nombre_administrador,
        'apellidos'        => $objeto->apellido_administrador,
        'fecha_nacimiento' => $nacimiento->diff($fecha_actual)->y,
        'cedula'           => $objeto->cedula_administrador,
        'tipo'             => $objeto->nombre_tipo_administrador,
        'telefono'         => $objeto->telefono_administrador,
        'direccion'        => $objeto->direccion_administrador,
        'correo'           => $objeto->correo_administrador,
        'sexo'             => $objeto->sexo_administrador,
        'adicional'        => $objeto->adicional_administrador,
        'avatar'           => '../../img/' . $objeto->avatar_administrador,
    ]);
    exit();
}

if ($funcion === 'capturar_datos') {
    $id_administrador = (int)($_POST['id_administrador'] ?? $_SESSION['usuario']);
    requireOwnership($id_administrador);

    $administrador->obtener_datos($id_administrador);
    if (empty($administrador->objetos)) {
        echo json_encode(['error' => 'No se encontró el administrador']);
        exit();
    }
    $objeto = $administrador->objetos[0];
    echo json_encode([
        'telefono'  => $objeto->telefono_administrador,
        'direccion' => $objeto->direccion_administrador,
        'correo'    => $objeto->correo_administrador,
        'sexo'      => $objeto->sexo_administrador,
        'adicional' => $objeto->adicional_administrador,
    ]);
    exit();
}

if ($funcion === 'editar_administrador') {
    $id_administrador = (int)($_POST['id_administrador'] ?? 0);
    requireOwnership($id_administrador);

    $administrador->editar(
        $id_administrador,
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
    $id_administrador = (int)$_SESSION['usuario'];

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
    $administrador->cambiar_photo($id_administrador, $nombre);

    if (!empty($administrador->objetos)) {
        foreach ($administrador->objetos as $objeto) {
            $anterior = $objeto->avatar_administrador ?? '';
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
