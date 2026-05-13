<?php
include_once 'Conexion.php';

class Receta {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        try {
            $db = new Conexion();
            $this->acceso = $db->pdo;
        } catch(PDOException $e) {
            error_log("Error de conexión en Receta: " . $e->getMessage());
            $this->acceso = null;
        }
    }
    
    // Obtener todas las recetas activas
  function obtener_recetas($id_medico = null) {
    if($this->acceso === null) return array();
    
    try {
        if ($id_medico) {
            $sql = "SELECT r.*, 
                           CONCAT(rp.nombre_paciente, ' ', rp.apellido_paciente) as nombre_paciente,
                           CONCAT(rm.nombre_medico, ' ', rm.apellido_medico) as nombre_medico
                    FROM recetas r
                    LEFT JOIN registro_paciente rp ON r.id_paciente = rp.id_paciente
                    LEFT JOIN registro_medico rm ON r.id_medico = rm.id_medico
                    WHERE r.estado = 1 AND r.id_medico = :id_medico
                    ORDER BY r.id_receta DESC";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id_medico' => $id_medico));
        } else {
            $sql = "SELECT r.*, 
                           CONCAT(rp.nombre_paciente, ' ', rp.apellido_paciente) as nombre_paciente,
                           CONCAT(rm.nombre_medico, ' ', rm.apellido_medico) as nombre_medico
                    FROM recetas r
                    LEFT JOIN registro_paciente rp ON r.id_paciente = rp.id_paciente
                    LEFT JOIN registro_medico rm ON r.id_medico = rm.id_medico
                    WHERE r.estado = 1
                    ORDER BY r.id_receta DESC";
            $query = $this->acceso->prepare($sql);
            $query->execute();
        }
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    } catch(PDOException $e) {
        error_log("Error en obtener_recetas: " . $e->getMessage());
        return array();
    }
}
    
    // Obtener una receta específica
    function obtener_receta($id_receta) {
        if($this->acceso === null) return array();
        
        try {
            $sql = "SELECT * FROM recetas WHERE id_receta = :id_receta AND estado = 1";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id_receta' => $id_receta));
            $this->objetos = $query->fetchAll();
            return $this->objetos;
        } catch(PDOException $e) {
            error_log("Error en obtener_receta: " . $e->getMessage());
            return array();
        }
    }
// Obtener recetas por paciente
function obtenerRecetasPorPaciente($id_paciente) {
    if($this->acceso === null) return array();
    
    try {
        $sql = "SELECT r.*, 
                       CONCAT(rm.nombre_medico, ' ', rm.apellido_medico) as nombre_medico
                FROM recetas r
                LEFT JOIN registro_medico rm ON r.id_medico = rm.id_medico
                WHERE r.estado = 1 AND r.id_paciente = :id_paciente
                ORDER BY r.id_receta DESC";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_paciente' => $id_paciente));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    } catch(PDOException $e) {
        error_log("Error en obtenerRecetasPorPaciente: " . $e->getMessage());
        return array();
    }
}

    
    // Crear nueva receta
   function crear_receta($nombre_medicamento, $marca, $cantidad, $dosis, $instrucciones, $id_paciente, $id_medico, $fecha_receta) {
        if($this->acceso === null) {
            echo 'error_conexion';
            return;
        }
        
        try {
            $sql = "INSERT INTO recetas(nombre_medicamento, marca, cantidad, dosis, instrucciones, id_paciente, id_medico, fecha_receta, estado) 
                    VALUES (:nombre_medicamento, :marca, :cantidad, :dosis, :instrucciones, :id_paciente, :id_medico, :fecha_receta, 1)";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':nombre_medicamento' => $nombre_medicamento,
                ':marca' => $marca,
                ':cantidad' => $cantidad,
                ':dosis' => $dosis,
                ':instrucciones' => $instrucciones,
                ':id_paciente' => $id_paciente,
                ':id_medico' => $id_medico,
                ':fecha_receta' => $fecha_receta
            ));
            echo 'creado';
        } catch(PDOException $e) {
            error_log("Error en crear_receta: " . $e->getMessage());
            echo 'error_bd';
        }
    }

    
    // Editar receta
    function editar_receta($id_receta, $nombre_medicamento, $marca, $cantidad, $dosis, $instrucciones, $id_paciente, $fecha_receta) {
        if($this->acceso === null) {
            echo 'error_conexion';
            return;
        }
        
        try {
            $sql = "UPDATE recetas SET 
                    nombre_medicamento = :nombre_medicamento,
                    marca = :marca,
                    cantidad = :cantidad,
                    dosis = :dosis,
                    instrucciones = :instrucciones,
                    id_paciente = :id_paciente,
                    fecha_receta = :fecha_receta
                    WHERE id_receta = :id_receta";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':id_receta' => $id_receta,
                ':nombre_medicamento' => $nombre_medicamento,
                ':marca' => $marca,
                ':cantidad' => $cantidad,
                ':dosis' => $dosis,
                ':instrucciones' => $instrucciones,
                ':id_paciente' => $id_paciente,
                ':fecha_receta' => $fecha_receta
            ));
            echo 'editado';
        } catch(PDOException $e) {
            error_log("Error en editar_receta: " . $e->getMessage());
            echo 'error_bd';
        }
    }
    
    // Borrado lógico (actualiza estado a 0)
    function borrar_receta($id_receta) {
        if($this->acceso === null) {
            echo 'error_conexion';
            return;
        }
        
        try {
            $sql = "UPDATE recetas SET estado = 0 WHERE id_receta = :id_receta";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id_receta' => $id_receta));
            echo 'borrado';
        } catch(PDOException $e) {
            error_log("Error en borrar_receta: " . $e->getMessage());
            echo 'error_bd';
        }
    }
    
    // Buscar pacientes por cédula o nombre
   
function buscar_pacientes($dato) {
    if($this->acceso === null) return array();
    
    try {      
        $sql = "SELECT id_paciente as id_usuario, 
                       nombre_paciente as nombre_us, 
                       apellido_paciente as apellidos_us, 
                       cedula_paciente as cedula_us,
                       fecha_nacimiento_pac as fecha_nacimiento,
                       sexo_paciente as sexo_us
                FROM registro_paciente 
                WHERE (cedula_paciente LIKE :dato OR nombre_paciente LIKE :dato) 
                LIMIT 10";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':dato' => "%$dato%"));
        $this->objetos = $query->fetchAll();
        
        // Calcular edad para cada paciente
        foreach($this->objetos as $paciente) {
            if(isset($paciente->fecha_nacimiento)) {
                $nacimiento = new DateTime($paciente->fecha_nacimiento);
                $hoy = new DateTime();
                $edad = $nacimiento->diff($hoy);
                $paciente->edad = $edad->y;
            } else {
                $paciente->edad = 0;
            }
        }
        
        return $this->objetos;
    } catch(PDOException $e) {
        error_log("Error en buscar_pacientes: " . $e->getMessage());
        return array();
    }
}
// Guardar diagnóstico
function guardarDiagnostico($id_receta, $diagnostico, $trat_sugerido) {
    if($this->acceso === null) {
        return ['success' => false, 'message' => 'Error de conexión'];
    }
    
    try {
        // Verificar si ya existe un diagnóstico para esta receta
        $sql_check = "SELECT id_diagnostico FROM diagnostico_rec WHERE id_receta = :id_receta";
        $query_check = $this->acceso->prepare($sql_check);
        $query_check->execute(array(':id_receta' => $id_receta));
        $existe = $query_check->fetch();
        
        if($existe) {
            // Actualizar diagnóstico existente
            $sql = "UPDATE diagnostico_rec SET diagnostico = :diagnostico, trat_sugerido = :trat_sugerido WHERE id_receta = :id_receta";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':id_receta' => $id_receta,
                ':diagnostico' => $diagnostico,
                ':trat_sugerido' => $trat_sugerido
            ));
        } else {
            // Insertar nuevo diagnóstico
            $sql = "INSERT INTO diagnostico_rec(id_receta, diagnostico, trat_sugerido) VALUES (:id_receta, :diagnostico, :trat_sugerido)";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':id_receta' => $id_receta,
                ':diagnostico' => $diagnostico,
                ':trat_sugerido' => $trat_sugerido
            ));
        }
        
        return ['success' => true, 'message' => 'Diagnóstico guardado correctamente'];
    } catch(PDOException $e) {
        error_log("Error en guardarDiagnostico: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al guardar diagnóstico: ' . $e->getMessage()];
    }
}

// Guardar estudio de laboratorio
function guardarEstudioLab($id_receta, $est_solicitado, $obs_adicional) {
    if($this->acceso === null) {
        return ['success' => false, 'message' => 'Error de conexión'];
    }
    
    try {
        // Verificar si ya existe un estudio para esta receta
        $sql_check = "SELECT id_estudio FROM est_laboratorio WHERE id_receta = :id_receta";
        $query_check = $this->acceso->prepare($sql_check);
        $query_check->execute(array(':id_receta' => $id_receta));
        $existe = $query_check->fetch();
        
        if($existe) {
            // Actualizar estudio existente
            $sql = "UPDATE est_laboratorio SET est_solicitado = :est_solicitado, obs_adicional = :obs_adicional WHERE id_receta = :id_receta";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':id_receta' => $id_receta,
                ':est_solicitado' => $est_solicitado,
                ':obs_adicional' => $obs_adicional
            ));
        } else {
            // Insertar nuevo estudio
            $sql = "INSERT INTO est_laboratorio(id_receta, est_solicitado, obs_adicional) VALUES (:id_receta, :est_solicitado, :obs_adicional)";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':id_receta' => $id_receta,
                ':est_solicitado' => $est_solicitado,
                ':obs_adicional' => $obs_adicional
            ));
        }
        
        return ['success' => true, 'message' => 'Estudio guardado correctamente'];
    } catch(PDOException $e) {
        error_log("Error en guardarEstudioLab: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al guardar estudio: ' . $e->getMessage()];
    }
}

// Obtener diagnóstico por ID de receta
function obtenerDiagnostico($id_receta) {
    if($this->acceso === null) return null;
    
    try {
        $sql = "SELECT * FROM diagnostico_rec WHERE id_receta = :id_receta";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_receta' => $id_receta));
        return $query->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error en obtenerDiagnostico: " . $e->getMessage());
        return null;
    }
}

// Obtener estudio por ID de receta
function obtenerEstudioLab($id_receta) {
    if($this->acceso === null) return null;
    
    try {
        $sql = "SELECT * FROM est_laboratorio WHERE id_receta = :id_receta";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_receta' => $id_receta));
        return $query->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error en obtenerEstudioLab: " . $e->getMessage());
        return null;
    }
}
}
?>