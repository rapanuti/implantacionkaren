<?php
include_once 'Conexion.php';

class Consultorio {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    
    // Listar todos los consultorios
    function listar($busqueda = '') {
        try {
            $sql = "SELECT c.*, 
                    (SELECT COUNT(*) FROM consultorio_medicos cm WHERE cm.id_consultorio = c.id_consultorio AND cm.activo = 1) as total_medicos
                    FROM consultorios c 
                    WHERE c.activo = 1";
            
            if(!empty($busqueda)) {
                $sql .= " AND (c.nombre LIKE :busqueda OR c.ciudad LIKE :busqueda OR c.direccion_detallada LIKE :busqueda)";
                $query = $this->acceso->prepare($sql);
                $query->execute(array(':busqueda' => "%$busqueda%"));
            } else {
                $query = $this->acceso->prepare($sql);
                $query->execute();
            }
            
            $this->objetos = $query->fetchAll();
            return $this->objetos;
        } catch(PDOException $e) {
            error_log("Error en listar consultorios: " . $e->getMessage());
            return array();
        }
    }
    
    // Obtener total de consultorios activos
    function totalActivos() {
        try {
            $sql = "SELECT COUNT(*) as total FROM consultorios WHERE activo = 1";
            $query = $this->acceso->prepare($sql);
            $query->execute();
            $resultado = $query->fetch();
            return $resultado->total ?? 0;
        } catch(PDOException $e) {
            return 0;
        }
    }
    
    // Obtener consultorio por ID
    function obtener($id_consultorio) {
        try {
            $sql = "SELECT c.* FROM consultorios c WHERE c.id_consultorio = :id AND c.activo = 1";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id' => $id_consultorio));
            $this->objetos = $query->fetchAll();
            return $this->objetos;
        } catch(PDOException $e) {
            return array();
        }
    }
    
    // Obtener especialidades del consultorio
    function obtenerEspecialidades($id_consultorio) {
        try {
            $sql = "SELECT especialidad FROM consultorio_especialidades WHERE id_consultorio = :id";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id' => $id_consultorio));
            return $query->fetchAll();
        } catch(PDOException $e) {
            return array();
        }
    }
    
    // Obtener médicos asignados al consultorio
    function obtenerMedicos($id_consultorio) {
        try {
            $sql = "SELECT cm.*, rm.nombre_medico, rm.apellido_medico, rm.cedula_medico, rm.telefono_medico
                    FROM consultorio_medicos cm
                    INNER JOIN registro_medico rm ON cm.id_medico = rm.id_medico
                    WHERE cm.id_consultorio = :id AND cm.activo = 1";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id' => $id_consultorio));
            return $query->fetchAll();
        } catch(PDOException $e) {
            return array();
        }
    }
    
    // Obtener horarios del consultorio
    function obtenerHorarios($id_consultorio) {
        try {
            $sql = "SELECT ch.*, 
                    CONCAT(rm.nombre_medico, ' ', rm.apellido_medico) as nombre_medico
                    FROM consultorio_horarios ch
                    LEFT JOIN registro_medico rm ON ch.id_medico = rm.id_medico
                    WHERE ch.id_consultorio = :id AND ch.activo = 1
                    ORDER BY FIELD(ch.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'), ch.turno";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id' => $id_consultorio));
            return $query->fetchAll();
        } catch(PDOException $e) {
            return array();
        }
    }
    
    function crear($nombre, $descripcion, $apertura, $cierre, $telefono, $email, 
               $id_estado, $id_ciudad, $id_municipio, $id_parroquia, $direccion, $especialidades) {
    try {
        // Obtener los nombres de ubicación para guardarlos también como texto
        $estado_nombre = $this->getNombreEstado($id_estado);
        $ciudad_nombre = $this->getNombreCiudad($id_ciudad);
        $municipio_nombre = $this->getNombreMunicipio($id_municipio);
        $parroquia_nombre = $this->getNombreParroquia($id_parroquia);
        
        $sql = "INSERT INTO consultorios(
                    nombre, descripcion, apertura_habitual, cierre_habitual, 
                    telefono, email, 
                    id_estado, estado, id_ciudad, ciudad, 
                    id_municipio, municipio, id_parroquia, parroquia, 
                    direccion_detallada
                ) VALUES (
                    :nombre, :descripcion, :apertura, :cierre,
                    :telefono, :email,
                    :id_estado, :estado_nombre, :id_ciudad, :ciudad_nombre,
                    :id_municipio, :municipio_nombre, :id_parroquia, :parroquia_nombre,
                    :direccion
                )";
        $query = $this->acceso->prepare($sql);
        $resultado = $query->execute(array(
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':apertura' => $apertura,
            ':cierre' => $cierre,
            ':telefono' => $telefono,
            ':email' => $email,
            ':id_estado' => $id_estado,
            ':estado_nombre' => $estado_nombre,
            ':id_ciudad' => $id_ciudad,
            ':ciudad_nombre' => $ciudad_nombre,
            ':id_municipio' => $id_municipio,
            ':municipio_nombre' => $municipio_nombre,
            ':id_parroquia' => $id_parroquia,
            ':parroquia_nombre' => $parroquia_nombre,
            ':direccion' => $direccion
        ));
        
        if($resultado) {
            $id_consultorio = $this->acceso->lastInsertId();
            
            // Insertar especialidades
            if(!empty($especialidades)) {
                foreach($especialidades as $especialidad) {
                    $sql_esp = "INSERT INTO consultorio_especialidades(id_consultorio, especialidad) VALUES (:id, :especialidad)";
                    $query_esp = $this->acceso->prepare($sql_esp);
                    $query_esp->execute(array(':id' => $id_consultorio, ':especialidad' => $especialidad));
                }
            }
            
            echo 'creado';
        } else {
            echo 'error';
        }
    } catch(PDOException $e) {
        error_log("Error en crear consultorio: " . $e->getMessage());
        echo 'error_bd';
    }
}

// Funciones auxiliares para obtener nombres
function getNombreEstado($id_estado) {
    $sql = "SELECT estado FROM estados WHERE id_estado = :id";
    $query = $this->acceso->prepare($sql);
    $query->execute(array(':id' => $id_estado));
    $resultado = $query->fetch();
    return $resultado ? $resultado->estado : '';
}

function getNombreCiudad($id_ciudad) {
    $sql = "SELECT ciudad FROM ciudades WHERE id_ciudad = :id";
    $query = $this->acceso->prepare($sql);
    $query->execute(array(':id' => $id_ciudad));
    $resultado = $query->fetch();
    return $resultado ? $resultado->ciudad : '';
}

function getNombreMunicipio($id_municipio) {
    $sql = "SELECT municipio FROM municipios WHERE id_municipio = :id";
    $query = $this->acceso->prepare($sql);
    $query->execute(array(':id' => $id_municipio));
    $resultado = $query->fetch();
    return $resultado ? $resultado->municipio : '';
}

function getNombreParroquia($id_parroquia) {
    $sql = "SELECT parroquia FROM parroquias WHERE id_parroquia = :id";
    $query = $this->acceso->prepare($sql);
    $query->execute(array(':id' => $id_parroquia));
    $resultado = $query->fetch();
    return $resultado ? $resultado->parroquia : '';
}
    
    // Editar consultorio
    function editar($id_consultorio, $nombre, $descripcion, $apertura, $cierre, $telefono, $email, $estado, $ciudad, $municipio, $parroquia, $direccion, $especialidades) {
        try {
            $sql = "UPDATE consultorios SET 
                    nombre = :nombre,
                    descripcion = :descripcion,
                    apertura_habitual = :apertura,
                    cierre_habitual = :cierre,
                    telefono = :telefono,
                    email = :email,
                    estado = :estado,
                    ciudad = :ciudad,
                    municipio = :municipio,
                    parroquia = :parroquia,
                    direccion_detallada = :direccion
                    WHERE id_consultorio = :id";
            $query = $this->acceso->prepare($sql);
            $resultado = $query->execute(array(
                ':id' => $id_consultorio,
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':apertura' => $apertura,
                ':cierre' => $cierre,
                ':telefono' => $telefono,
                ':email' => $email,
                ':estado' => $estado,
                ':ciudad' => $ciudad,
                ':municipio' => $municipio,
                ':parroquia' => $parroquia,
                ':direccion' => $direccion
            ));
            
            if($resultado) {
                // Actualizar especialidades
                $sql_del = "DELETE FROM consultorio_especialidades WHERE id_consultorio = :id";
                $query_del = $this->acceso->prepare($sql_del);
                $query_del->execute(array(':id' => $id_consultorio));
                
                if(!empty($especialidades)) {
                    foreach($especialidades as $especialidad) {
                        $sql_esp = "INSERT INTO consultorio_especialidades(id_consultorio, especialidad) VALUES (:id, :especialidad)";
                        $query_esp = $this->acceso->prepare($sql_esp);
                        $query_esp->execute(array(':id' => $id_consultorio, ':especialidad' => $especialidad));
                    }
                }
                
                echo 'editado';
            } else {
                echo 'error';
            }
        } catch(PDOException $e) {
            error_log("Error en editar consultorio: " . $e->getMessage());
            echo 'error_bd';
        }
    }
    
    // Eliminar consultorio (borrado lógico)
    function eliminar($id_consultorio) {
        try {
            $sql = "UPDATE consultorios SET activo = 0 WHERE id_consultorio = :id";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id' => $id_consultorio));
            echo 'eliminado';
        } catch(PDOException $e) {
            echo 'error';
        }
    }
    
    // Asignar médico a consultorio
    function asignarMedico($id_consultorio, $id_medico) {
        try {
            // Verificar si ya existe
            $sql_check = "SELECT id FROM consultorio_medicos WHERE id_consultorio = :id_consultorio AND id_medico = :id_medico AND activo = 1";
            $query_check = $this->acceso->prepare($sql_check);
            $query_check->execute(array(':id_consultorio' => $id_consultorio, ':id_medico' => $id_medico));
            
            if($query_check->rowCount() > 0) {
                echo 'ya_asignado';
                return;
            }
            
            $sql = "INSERT INTO consultorio_medicos(id_consultorio, id_medico, fecha_asignacion) VALUES (:id_consultorio, :id_medico, CURDATE())";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id_consultorio' => $id_consultorio, ':id_medico' => $id_medico));
            echo 'asignado';
        } catch(PDOException $e) {
            echo 'error';
        }
    }
    
    // Eliminar asignación de médico
    function removerMedico($id_asignacion) {
        try {
            $sql = "UPDATE consultorio_medicos SET activo = 0 WHERE id = :id";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id' => $id_asignacion));
            echo 'removido';
        } catch(PDOException $e) {
            echo 'error';
        }
    }
    
    // Guardar horario
    function guardarHorario($id_consultorio, $dia, $turno, $hora_inicio, $hora_fin, $id_medico = null) {
        try {
            // Verificar si ya existe horario para ese día/turno
            $sql_check = "SELECT id_horario FROM consultorio_horarios WHERE id_consultorio = :id_consultorio AND dia_semana = :dia AND turno = :turno AND activo = 1";
            $query_check = $this->acceso->prepare($sql_check);
            $query_check->execute(array(':id_consultorio' => $id_consultorio, ':dia' => $dia, ':turno' => $turno));
            
            if($query_check->rowCount() > 0) {
                // Actualizar existente
                $sql = "UPDATE consultorio_horarios SET hora_inicio = :hora_inicio, hora_fin = :hora_fin, id_medico = :id_medico 
                        WHERE id_consultorio = :id_consultorio AND dia_semana = :dia AND turno = :turno";
                $query = $this->acceso->prepare($sql);
                $query->execute(array(
                    ':id_consultorio' => $id_consultorio,
                    ':dia' => $dia,
                    ':turno' => $turno,
                    ':hora_inicio' => $hora_inicio,
                    ':hora_fin' => $hora_fin,
                    ':id_medico' => $id_medico
                ));
            } else {
                // Insertar nuevo
                $sql = "INSERT INTO consultorio_horarios(id_consultorio, dia_semana, turno, hora_inicio, hora_fin, id_medico) 
                        VALUES (:id_consultorio, :dia, :turno, :hora_inicio, :hora_fin, :id_medico)";
                $query = $this->acceso->prepare($sql);
                $query->execute(array(
                    ':id_consultorio' => $id_consultorio,
                    ':dia' => $dia,
                    ':turno' => $turno,
                    ':hora_inicio' => $hora_inicio,
                    ':hora_fin' => $hora_fin,
                    ':id_medico' => $id_medico
                ));
            }
            echo 'guardado';
        } catch(PDOException $e) {
            echo 'error';
        }
    }
    
    // Listar todos los médicos para asignar
    function listarMedicos() {
        try {
            $sql = "SELECT id_medico, nombre_medico, apellido_medico, cedula_medico FROM registro_medico WHERE medico_tipo = 2 ORDER BY nombre_medico";
            $query = $this->acceso->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch(PDOException $e) {
            return array();
        }
    }
    
    //  lista de especialidades predefinidas
    function obtenerListaEspecialidades() {
        return [
            'Cardiología', 'Pediatría', 'Dermatología', 'Ginecología', 'Traumatología',
            'Oftalmología', 'Medicina General', 'Neurología', 'Psiquiatría', 'Gastroenterología',
            'Urología', 'Otorrinolaringología', 'Neumología', 'Endocrinología', 'Reumatología',
            'Nefrología', 'Oncología', 'Hematología', 'Medicina Interna', 'Anestesiología', 'Radiología'
        ];
    }
    // Listar estados
function listarEstados() {
    try {
        $sql = "SELECT id_estado, estado FROM estados ORDER BY estado";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    } catch(PDOException $e) {
        return array();
    }
}

// Listar ciudades por estado
function listarCiudades($id_estado) {
    try {
        $sql = "SELECT id_ciudad, ciudad FROM ciudades WHERE id_estado = :id_estado ORDER BY ciudad";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_estado' => $id_estado));
        return $query->fetchAll();
    } catch(PDOException $e) {
        return array();
    }
}

// Listar municipios por estado (NO por ciudad, porque municipio está relacionado con estado)
function listarMunicipios($id_estado) {
    try {
        $sql = "SELECT id_municipio, municipio FROM municipios WHERE id_estado = :id_estado ORDER BY municipio";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_estado' => $id_estado));
        return $query->fetchAll();
    } catch(PDOException $e) {
        return array();
    }
}
// Verificar si un médico ya tiene horario en el mismo día/turno
function verificarHorarioMedico($id_medico, $dia, $turno, $id_consultorio = null) {
    try {
        $sql = "SELECT ch.*, c.nombre as consultorio_nombre 
                FROM consultorio_horarios ch
                INNER JOIN consultorios c ON ch.id_consultorio = c.id_consultorio
                WHERE ch.id_medico = :id_medico 
                AND ch.dia_semana = :dia 
                AND ch.turno = :turno 
                AND ch.activo = 1";
        
        if ($id_consultorio) {
            $sql .= " AND ch.id_consultorio != :id_consultorio";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':id_medico' => $id_medico,
                ':dia' => $dia,
                ':turno' => $turno,
                ':id_consultorio' => $id_consultorio
            ));
        } else {
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':id_medico' => $id_medico,
                ':dia' => $dia,
                ':turno' => $turno
            ));
        }
        
        return $query->fetchAll();
    } catch(PDOException $e) {
        return array();
    }
}

// Listar parroquias por municipio
function listarParroquias($id_municipio) {
    try {
        $sql = "SELECT id_parroquia, parroquia FROM parroquias WHERE id_municipio = :id_municipio ORDER BY parroquia";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_municipio' => $id_municipio));
        return $query->fetchAll();
    } catch(PDOException $e) {
        return array();
    }
}
}
?>
