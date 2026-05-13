<?php
include_once 'Conexion.php';

class Medico {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    
    function obtener_datos($id) {
        $sql = "SELECT rm.*, tp.nombre_tipo 
                FROM registro_medico rm
                INNER JOIN tipo_paciente tp ON rm.medico_tipo = tp.id_tipo_us 
                WHERE rm.id_medico = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
    function editar($id_medico, $telefono, $direccion, $correo, $sexo, $adicional) {
        $sql = "UPDATE registro_medico SET 
                telefono_medico = :telefono,
                direccion_medico = :direccion,
                correo_medico = :correo,
                sexo_medico = :sexo,
                adicional_medico = :adicional 
                WHERE id_medico = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id' => $id_medico,
            ':telefono' => $telefono,
            ':direccion' => $direccion,
            ':correo' => $correo,
            ':sexo' => $sexo,
            ':adicional' => $adicional
        ));
    }
    
    function cambiar_photo($id_medico, $nombre) {   
        $sql = "SELECT avatar_medico FROM registro_medico WHERE id_medico = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id_medico));
        $this->objetos = $query->fetchAll();    
        
        $sql = "UPDATE registro_medico SET avatar_medico = :nombre WHERE id_medico = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id_medico, ':nombre' => $nombre));  
        return $this->objetos;   
    }
    
    function buscar() {
        if(!empty($_POST['consulta'])) {
            $consulta = $_POST['consulta'];
            $sql = "SELECT rm.*, tp.nombre_tipo 
                    FROM registro_medico rm 
                    JOIN tipo_paciente tp ON rm.medico_tipo = tp.id_tipo_us 
                    WHERE rm.nombre_medico LIKE :consulta";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':consulta' => "%$consulta%"));
            $this->objetos = $query->fetchAll();
            return $this->objetos;
        } else {         
            $sql = "SELECT rm.*, tp.nombre_tipo 
                    FROM registro_medico rm 
                    JOIN tipo_paciente tp ON rm.medico_tipo = tp.id_tipo_us 
                    WHERE rm.nombre_medico NOT LIKE '' 
                    ORDER BY rm.id_medico LIMIT 25";
            $query = $this->acceso->prepare($sql);
            $query->execute();
            $this->objetos = $query->fetchAll();
            return $this->objetos;
        }
    }
    
    function crear($nombre, $apellidos, $fecha_nacimiento, $cedula, $telefono, $direccion, $correo, $sexo, $adicional, $password_hash, $tipo, $avatar) {
        try {
            $sql = "SELECT id_medico FROM registro_medico WHERE cedula_medico = :cedula OR correo_medico = :correo";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':cedula' => $cedula, ':correo' => $correo));
            $this->objetos = $query->fetchAll();
            
            if(!empty($this->objetos)) {
                echo 'existe';
                return;
            }
            
            $sql = "INSERT INTO registro_medico(
                nombre_medico, apellido_medico, fecha_nacimiento_medico, 
                cedula_medico, telefono_medico, direccion_medico, 
                correo_medico, sexo_medico, adicional_medico, 
                avatar_medico, medico_tipo
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
                $id_medico = $this->acceso->lastInsertId();
                $this->crearLogin($id_medico, $password_hash);
                echo 'add';
            } else {
                echo 'error_bd';
            }
        } catch(PDOException $e) {
            error_log("Error en crear medico: " . $e->getMessage());
            echo 'error_exception';
        }
    }
    
    function crearLogin($id_medico, $password_hash) {
        $sql = "INSERT INTO login_medico(id_medico, password_hash, status) 
                VALUES (:id_medico, :password_hash, 'activo')";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id_medico' => $id_medico,
            ':password_hash' => $password_hash
        ));
    }
        // Contar recetas del médico
function contarRecetas($id_medico) {
    if($this->acceso === null) return 0;
    
    try {
        $sql = "SELECT COUNT(*) as total FROM recetas WHERE id_medico = :id_medico AND estado = 1";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_medico' => $id_medico));
        $resultado = $query->fetch(PDO::FETCH_OBJ);
        return $resultado->total ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

// Contar pacientes del médico
function contarPacientes($id_medico) {
    if($this->acceso === null) return 0;
    
    try {
        $sql = "SELECT COUNT(DISTINCT id_paciente) as total FROM recetas WHERE id_medico = :id_medico AND estado = 1 AND id_paciente IS NOT NULL";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_medico' => $id_medico));
        $resultado = $query->fetch(PDO::FETCH_OBJ);
        return $resultado->total ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

// Listar pacientes del médico
function listarPacientes($id_medico) {
    if($this->acceso === null) return array();
    
    try {
        $sql = "SELECT DISTINCT 
                    rp.id_paciente, 
                    rp.nombre_paciente as nombre, 
                    rp.apellido_paciente as apellidos, 
                    rp.cedula_paciente as cedula, 
                    rp.telefono_paciente as telefono, 
                    rp.correo_paciente as correo
                FROM recetas r
                INNER JOIN registro_paciente rp ON r.id_paciente = rp.id_paciente
                WHERE r.id_medico = :id_medico AND r.estado = 1
                ORDER BY rp.nombre_paciente ASC";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_medico' => $id_medico));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    } catch(PDOException $e) {
        return array();
    }
}
}
?>
