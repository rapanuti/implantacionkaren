<?php
include_once 'Conexion.php';

class Administrador {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    
    function obtener_datos($id) {
        $sql = "SELECT ra.*, tp.nombre_tipo 
                FROM registro_administrador ra
                INNER JOIN tipo_paciente tp ON ra.administrador_tipo = tp.id_tipo_us 
                WHERE ra.id_administrador = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
    function editar($id_administrador, $telefono, $direccion, $correo, $sexo, $adicional) {
        $sql = "UPDATE registro_administrador SET 
                telefono_administrador = :telefono,
                direccion_administrador = :direccion,
                correo_administrador = :correo,
                sexo_administrador = :sexo,
                adicional_administrador = :adicional 
                WHERE id_administrador = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id' => $id_administrador,
            ':telefono' => $telefono,
            ':direccion' => $direccion,
            ':correo' => $correo,
            ':sexo' => $sexo,
            ':adicional' => $adicional
        ));
    }
    
    function cambiar_photo($id_administrador, $nombre) {   
        $sql = "SELECT avatar_administrador FROM registro_administrador WHERE id_administrador = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id_administrador));
        $this->objetos = $query->fetchAll();    
        
        $sql = "UPDATE registro_administrador SET avatar_administrador = :nombre WHERE id_administrador = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id_administrador, ':nombre' => $nombre));  
        return $this->objetos;   
    }
    
    function crear($nombre, $apellidos, $fecha_nacimiento, $cedula, $telefono, $direccion, $correo, $sexo, $adicional, $password_hash, $tipo, $avatar) {
        try {
            $sql = "SELECT id_administrador FROM registro_administrador WHERE cedula_administrador = :cedula OR correo_administrador = :correo";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':cedula' => $cedula, ':correo' => $correo));
            $this->objetos = $query->fetchAll();
            
            if(!empty($this->objetos)) {
                echo 'existe';
                return;
            }
            
            $sql = "INSERT INTO registro_administrador(
                nombre_administrador, apellido_administrador, fecha_nacimiento_administrador, 
                cedula_administrador, telefono_administrador, direccion_administrador, 
                correo_administrador, sexo_administrador, adicional_administrador, 
                avatar_administrador, administrador_tipo
            ) VALUES (
                :nombre, :apellidos, :fecha_nacimiento,
                :cedula, :telefono, :direccion,
                :correo, :sexo, :adicional,
                :avatar, :tipo
            )";
            $query = $this->acceso->prepare($sql);
            $resultado = $query->execute(array(
                ':nombre' => $nombre,
                ':apellidos' => $apellidos,
                ':fecha_nacimiento' => $fecha_nacimiento,
                ':cedula' => $cedula,
                ':telefono' => $telefono,
                ':direccion' => $direccion,
                ':correo' => $correo,
                ':sexo' => $sexo,
                ':adicional' => $adicional,
                ':avatar' => $avatar,
                ':tipo' => $tipo
            ));
            
            if($resultado) {
                $id_administrador = $this->acceso->lastInsertId();
                $this->crearLogin($id_administrador, $password_hash);
                echo 'add';
            } else {
                echo 'error_bd';
            }
        } catch(PDOException $e) {
            error_log("Error en crear administrador: " . $e->getMessage());
            echo 'error_exception';
        }
    }
    
    function crearLogin($id_administrador, $password_hash) {
        $sql = "INSERT INTO login_administrador(id_administrador, password_hash, status) 
                VALUES (:id_administrador, :password_hash, 'activo')";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id_administrador' => $id_administrador,
            ':password_hash' => $password_hash
        ));
    }
}
?>
