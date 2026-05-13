<?php
include_once '../modelo/LoginAdministrador.php';
session_start();

$login = new LoginAdministrador();
$funcion = $_POST['funcion'] ?? '';

if ($funcion == 'cambiar_contra') {
    $id_administrador = $_POST['id_administrador'];
    $oldpass = $_POST['oldpass'];
    $newpass = $_POST['newpass'];
    
    ob_start();
    $login->cambiar_contra($id_administrador, $oldpass, $newpass);
    $resultado = ob_get_clean();
    echo json_encode(['resultado' => trim($resultado)]);
    exit();
}
?>
