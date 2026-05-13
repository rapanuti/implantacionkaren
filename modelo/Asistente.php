<?php
include_once 'Conexion.php';

class Asistente {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    
    function obtener_datos($id) {
        $sql = "SELECT ra.*, tp.nombre_tipo 
                FROM registro_asistente ra
                INNER JOIN tipo_paciente tp ON ra.asistente_tipo = tp.id_tipo_us 
                WHERE ra.id_asistente = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
    function editar($id_asistente, $telefono, $direccion, $correo, $sexo, $adicional) {
        $sql = "UPDATE registro_asistente SET 
                telefono_asistente = :telefono,
                direccion_asistente = :direccion,
                correo_asistente = :correo,
                sexo_asistente = :sexo,
                adicional_asistente = :adicional 
                WHERE id_asistente = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id' => $id_asistente,
            ':telefono' => $telefono,
            ':direccion' => $direccion,
            ':correo' => $correo,
            ':sexo' => $sexo,
            ':adicional' => $adicional
        ));
    }
    
    function cambiar_photo($id_asistente, $nombre) {   
        $sql = "SELECT avatar_asistente FROM registro_asistente WHERE id_asistente = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id_asistente));
        $this->objetos = $query->fetchAll();    
        
        $sql = "UPDATE registro_asistente SET avatar_asistente = :nombre WHERE id_asistente = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id_asistente, ':nombre' => $nombre));  
        return $this->objetos;   
    }
    
    function buscar() {
        if(!empty($_POST['consulta'])) {
            $consulta = $_POST['consulta'];
            $sql = "SELECT ra.*, tp.nombre_tipo 
                    FROM registro_asistente ra 
                    JOIN tipo_paciente tp ON ra.asistente_tipo = tp.id_tipo_us 
                    WHERE ra.nombre_asistente LIKE :consulta";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':consulta' => "%$consulta%"));
            $this->objetos = $query->fetchAll();
            return $this->objetos;
        } else {         
            $sql = "SELECT ra.*, tp.nombre_tipo 
                    FROM registro_asistente ra 
                    JOIN tipo_paciente tp ON ra.asistente_tipo = tp.id_tipo_us 
                    WHERE ra.nombre_asistente NOT LIKE '' 
                    ORDER BY ra.id_asistente LIMIT 25";
            $query = $this->acceso->prepare($sql);
            $query->execute();
            $this->objetos = $query->fetchAll();
            return $this->objetos;
        }
    }
    
    function crear($nombre, $apellidos, $fecha_nacimiento, $cedula, $telefono, $direccion, $correo, $sexo, $adicional, $password_hash, $tipo, $avatar) {
        try {
            $sql = "SELECT id_asistente FROM registro_asistente WHERE cedula_asistente = :cedula OR correo_asistente = :correo";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':cedula' => $cedula, ':correo' => $correo));
            $this->objetos = $query->fetchAll();
            
            if(!empty($this->objetos)) {
                echo 'existe';
                return;
            }
            
            $sql = "INSERT INTO registro_asistente(
                nombre_asistente, apellido_asistente, fecha_nacimiento_asistente, 
                cedula_asistente, telefono_asistente, direccion_asistente, 
                correo_asistente, sexo_asistente, adicional_asistente, 
                avatar_asistente, asistente_tipo
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
                $id_asistente = $this->acceso->lastInsertId();
                $this->crearLogin($id_asistente, $password_hash);
                echo 'add';
            } else {
                echo 'error_bd';
            }
        } catch(PDOException $e) {
            error_log("Error en crear asistente: " . $e->getMessage());
            echo 'error_exception';
        }
    }
    
    function crearLogin($id_asistente, $password_hash) {
        $sql = "INSERT INTO login_asistente(id_asistente, password_hash, status) 
                VALUES (:id_asistente, :password_hash, 'activo')";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id_asistente' => $id_asistente,
            ':password_hash' => $password_hash
        ));
    }
}
?>
