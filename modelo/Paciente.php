<?php
include_once 'Conexion.php';

class Paciente {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    
    function crear($nombre, $apellidos, $fecha_nacimiento, $cedula, $telefono, $direccion, $correo, $sexo, $adicional, $password_hash, $tipo, $avatar) {
        try {
            // Verificar si el usuario ya existe
            $sql = "SELECT id_paciente FROM registro_paciente WHERE cedula_paciente = :cedula OR correo_paciente = :correo";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':cedula' => $cedula, ':correo' => $correo));
            $this->objetos = $query->fetchAll();
            
            if(!empty($this->objetos)) {
                echo 'existe';
                return;
            }
            
            // Crear nuevo paciente
            $sql = "INSERT INTO registro_paciente(
                nombre_paciente, apellido_paciente, fecha_nacimiento_pac, 
                cedula_paciente, telefono_paciente, direccion_paciente, 
                correo_paciente, sexo_paciente, adicional_paciente, 
                avatar_paciente, paciente_tipo
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
                $id_paciente = $this->acceso->lastInsertId();
                $this->crearLogin($id_paciente, $password_hash);
                echo 'add';
            } else {
                echo 'error_bd';
            }
            
        } catch(PDOException $e) {
            error_log("Error en crear paciente: " . $e->getMessage());
            echo 'error_exception';
        }
    }
    
    function crearLogin($id_paciente, $password_hash) {
        $sql = "INSERT INTO login_paciente(id_paciente, password_hash, status) 
                VALUES (:id_paciente, :password_hash, 'activo')";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id_paciente' => $id_paciente,
            ':password_hash' => $password_hash
        ));
    }
    // Obtener datos del paciente
function obtener_datos($id) {
    $sql = "SELECT rp.*, tp.nombre_tipo 
            FROM registro_paciente rp
            INNER JOIN tipo_paciente tp ON rp.paciente_tipo = tp.id_tipo_us 
            WHERE rp.id_paciente = :id";
    $query = $this->acceso->prepare($sql);
    $query->execute(array(':id' => $id));
    $this->objetos = $query->fetchAll();
    return $this->objetos;
}

function editar($id_paciente, $telefono, $direccion, $correo, $sexo, $adicional) {
    $sql = "UPDATE registro_paciente SET 
            telefono_paciente = :telefono,
            direccion_paciente = :direccion,
            correo_paciente = :correo,
            sexo_paciente = :sexo,
            adicional_paciente = :adicional 
            WHERE id_paciente = :id";
    $query = $this->acceso->prepare($sql);
    $query->execute(array(
        ':id' => $id_paciente,
        ':telefono' => $telefono,
        ':direccion' => $direccion,  // ← Ya debería estar
        ':correo' => $correo,
        ':sexo' => $sexo,
        ':adicional' => $adicional
    ));
}

function cambiar_photo($id_paciente, $nombre) {   
    $sql = "SELECT avatar_paciente FROM registro_paciente WHERE id_paciente = :id";
    $query = $this->acceso->prepare($sql);
    $query->execute(array(':id' => $id_paciente));
    $this->objetos = $query->fetchAll();    
    $sql = "UPDATE registro_paciente SET avatar_paciente = :nombre WHERE id_paciente = :id";
    $query = $this->acceso->prepare($sql);
    $query->execute(array(':id' => $id_paciente, ':nombre' => $nombre));  
    return $this->objetos;   
}

// Contar recetas del paciente
function contarRecetas($id_paciente) {
    if($this->acceso === null) return 0;
    
    try {
        $sql = "SELECT COUNT(*) as total FROM recetas WHERE id_paciente = :id_paciente AND estado = 1";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_paciente' => $id_paciente));
        $resultado = $query->fetch(PDO::FETCH_OBJ);
        return $resultado->total ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

// Contar próximas citas (placeholder - puedes implementar según necesidad)
function contarProximasCitas($id_paciente) {
    // Por ahora retorna 0, puedes implementar cuando tengas tabla de citas
    return 0;
}
}
?>