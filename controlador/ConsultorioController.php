<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-02, PROBLEMA-04
 *   requireAuth() verifica sesión y rol de administrador.
 *   validateCsrf() en todas las peticiones POST.
 */
error_reporting(0);
ini_set('display_errors', 0);

include_once '../modelo/Consultorio.php';
require_once 'auth_middleware.php';

requireAuth('administrador');   // C5: Solo administradores
validateCsrf();                 // D4: Validar CSRF

header('Content-Type: application/json');

// Mantener compatibilidad con código que usa $is_ajax
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$consultorio = new Consultorio();

if(isset($_POST['funcion'])) {
    $funcion = $_POST['funcion'];
    
    // Listar consultorios
    if ($funcion == 'listar_consultorios') {
        $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';
        $resultados = $consultorio->listar($busqueda);
        $json = array();
        
        if(!empty($resultados)) {
            foreach ($resultados as $objeto) {
                $json[] = array(
                    'id_consultorio' => $objeto->id_consultorio,
                    'nombre' => $objeto->nombre,
                    'ciudad' => $objeto->ciudad,
                    'direccion' => $objeto->direccion_detallada,
                    'telefono' => $objeto->telefono,
                    'email' => $objeto->email,
                    'total_medicos' => $objeto->total_medicos ?? 0,
                    'apertura' => $objeto->apertura_habitual,
                    'cierre' => $objeto->cierre_habitual
                );
            }
        }
        echo json_encode($json);
        exit();
    }
    
    // Obtener estadísticas
    if ($funcion == 'obtener_estadisticas') {
        $total_consultorios = $consultorio->totalActivos();
        echo json_encode([
            'total_consultorios' => $total_consultorios,
            'activos' => $total_consultorios
        ]);
        exit();
    }
    
    // Obtener detalle de consultorio
    if ($funcion == 'obtener_detalle') {
        $id_consultorio = $_POST['id_consultorio'];
        $consultorio->obtener($id_consultorio);
        $detalle = array();
        
        if(!empty($consultorio->objetos)) {
            foreach ($consultorio->objetos as $objeto) {
                $detalle = array(
                    'id_consultorio' => $objeto->id_consultorio,
                    'nombre' => $objeto->nombre,
                    'descripcion' => $objeto->descripcion,
                    'apertura' => $objeto->apertura_habitual,
                    'cierre' => $objeto->cierre_habitual,
                    'telefono' => $objeto->telefono,
                    'email' => $objeto->email,
                    'estado' => $objeto->estado,
                    'ciudad' => $objeto->ciudad,
                    'municipio' => $objeto->municipio,
                    'parroquia' => $objeto->parroquia,
                    'direccion' => $objeto->direccion_detallada
                );
            }
        }
        
        // Obtener especialidades
        $especialidades = $consultorio->obtenerEspecialidades($id_consultorio);
        $lista_especialidades = array();
        foreach($especialidades as $esp) {
            $lista_especialidades[] = $esp->especialidad;
        }
        $detalle['especialidades'] = $lista_especialidades;
        
        // Obtener médicos
        $medicos = $consultorio->obtenerMedicos($id_consultorio);
        $lista_medicos = array();
        foreach($medicos as $med) {
            $lista_medicos[] = array(
                'id' => $med->id,
                'id_medico' => $med->id_medico,
                'nombre' => $med->nombre_medico . ' ' . $med->apellido_medico,
                'cedula' => $med->cedula_medico,
                'telefono' => $med->telefono_medico
            );
        }
        $detalle['medicos'] = $lista_medicos;
        
        echo json_encode($detalle);
        exit();
    }
    
    // Obtener horarios
    if ($funcion == 'obtener_horarios') {
        $id_consultorio = $_POST['id_consultorio'];
        $horarios = $consultorio->obtenerHorarios($id_consultorio);
        
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $turnos = ['Mañana', 'Tarde'];
        
        $horarios_organizados = array();
        foreach($dias as $dia) {
            $horarios_organizados[$dia] = array('Mañana' => null, 'Tarde' => null);
            foreach($turnos as $turno) {
                foreach($horarios as $hor) {
                    if($hor->dia_semana == $dia && $hor->turno == $turno) {
                        $horarios_organizados[$dia][$turno] = array(
                            'id_horario' => $hor->id_horario,
                            'hora_inicio' => substr($hor->hora_inicio, 0, 5),
                            'hora_fin' => substr($hor->hora_fin, 0, 5),
                            'id_medico' => $hor->id_medico,
                            'nombre_medico' => $hor->nombre_medico
                        );
                    }
                }
            }
        }
        
        // Obtener lista de médicos para asignar
        $medicos = $consultorio->listarMedicos();
        $lista_medicos = array();
        foreach($medicos as $med) {
            $lista_medicos[] = array(
                'id' => $med->id_medico,
                'nombre' => $med->nombre_medico . ' ' . $med->apellido_medico
            );
        }
        
        echo json_encode([
            'horarios' => $horarios_organizados,
            'medicos' => $lista_medicos
        ]);
        exit();
    }
    
   // Guardar horario con validaciones
if ($funcion == 'guardar_horario') {
    $id_consultorio = $_POST['id_consultorio'];
    $dia = $_POST['dia'];
    $turno = $_POST['turno'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $id_medico = !empty($_POST['id_medico']) ? $_POST['id_medico'] : null;
    
    // VALIDACIÓN 1: Verificar que hora fin sea mayor que hora inicio
    if ($hora_inicio >= $hora_fin) {
        echo json_encode(['resultado' => 'error_horario', 'mensaje' => 'La hora de fin debe ser mayor que la hora de inicio']);
        exit();
    }
    
    // VALIDACIÓN 2: Verificar horario duplicado para el mismo médico en el mismo día/turno
    if ($id_medico && !empty($id_medico)) {
        $sql_check_medico = "SELECT id_horario FROM consultorio_horarios 
                              WHERE id_medico = :id_medico 
                              AND dia_semana = :dia 
                              AND turno = :turno 
                              AND activo = 1
                              AND id_consultorio != :id_consultorio";
        $query_check = $consultorio->acceso->prepare($sql_check_medico);
        $query_check->execute(array(
            ':id_medico' => $id_medico,
            ':dia' => $dia,
            ':turno' => $turno,
            ':id_consultorio' => $id_consultorio
        ));
        
        if ($query_check->rowCount() > 0) {
            echo json_encode(['resultado' => 'error_duplicado', 'mensaje' => 'Este médico ya tiene un horario asignado en el mismo día y turno en otro consultorio']);
            exit();
        }
    }
    
    ob_start();
    $consultorio->guardarHorario($id_consultorio, $dia, $turno, $hora_inicio, $hora_fin, $id_medico);
    $resultado = ob_get_clean();
    
    echo json_encode(['resultado' => trim($resultado)]);
    exit();
}
    
   // Crear consultorio
if ($funcion == 'crear_consultorio') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $apertura = $_POST['apertura'];
    $cierre = $_POST['cierre'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $id_estado = $_POST['id_estado'];
    $id_ciudad = $_POST['id_ciudad'];
    $id_municipio = $_POST['id_municipio'] ?? null;
    $id_parroquia = $_POST['id_parroquia'] ?? null;
    $direccion = $_POST['direccion'];
    $especialidades = isset($_POST['especialidades']) ? $_POST['especialidades'] : array();
    
    ob_start();
    $consultorio->crear($nombre, $descripcion, $apertura, $cierre, $telefono, $email, 
                        $id_estado, $id_ciudad, $id_municipio, $id_parroquia, $direccion, $especialidades);
    $resultado = ob_get_clean();
    
    echo json_encode(['resultado' => trim($resultado)]);
    exit();
}
    
    // Editar consultorio
    if ($funcion == 'editar_consultorio') {
      $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $apertura = $_POST['apertura'];
    $cierre = $_POST['cierre'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $id_estado = $_POST['id_estado'];
    $id_ciudad = $_POST['id_ciudad'];
    $id_municipio = $_POST['id_municipio'] ?? null;
    $id_parroquia = $_POST['id_parroquia'] ?? null;
    $direccion = $_POST['direccion'];
    $especialidades = isset($_POST['especialidades']) ? $_POST['especialidades'] : array();
    
    ob_start();
    $consultorio->crear($nombre, $descripcion, $apertura, $cierre, $telefono, $email, 
                        $id_estado, $id_ciudad, $id_municipio, $id_parroquia, $direccion, $especialidades);
    $resultado = ob_get_clean();
    
    echo json_encode(['resultado' => trim($resultado)]);
    exit();
    }
    
    // Eliminar consultorio
    if ($funcion == 'eliminar_consultorio') {
        $id_consultorio = $_POST['id_consultorio'];
        ob_start();
        $consultorio->eliminar($id_consultorio);
        $resultado = ob_get_clean();
        
        echo json_encode(['resultado' => trim($resultado)]);
        exit();
    }
    
    // Listar médicos para asignar
    if ($funcion == 'listar_medicos_disponibles') {
        $medicos = $consultorio->listarMedicos();
        echo json_encode($medicos);
        exit();
    }
    
    // Asignar médico
    if ($funcion == 'asignar_medico') {
        $id_consultorio = $_POST['id_consultorio'];
        $id_medico = $_POST['id_medico'];
        ob_start();
        $consultorio->asignarMedico($id_consultorio, $id_medico);
        $resultado = ob_get_clean();
        echo json_encode(['resultado' => trim($resultado)]);
        exit();
    }
    
    // Remover médico
    if ($funcion == 'remover_medico') {
        $id_asignacion = $_POST['id_asignacion'];
        ob_start();
        $consultorio->removerMedico($id_asignacion);
        $resultado = ob_get_clean();
        echo json_encode(['resultado' => trim($resultado)]);
        exit();
    }
    
    // Obtener lista de especialidades predefinidas
    if ($funcion == 'lista_especialidades') {
        $especialidades = $consultorio->obtenerListaEspecialidades();
        echo json_encode($especialidades);
        exit();
    }
    // Listar estados
    if ($funcion == 'listar_estados') {
        $estados = $consultorio->listarEstados();
        echo json_encode($estados);
        exit();
    }

    // Listar ciudades por estado
    if ($funcion == 'listar_ciudades') {
        $id_estado = $_POST['id_estado'];
        $ciudades = $consultorio->listarCiudades($id_estado);
        echo json_encode($ciudades);
        exit();
    }

    // Listar municipios por estado
    if ($funcion == 'listar_municipios') {
        $id_estado = $_POST['id_estado'];
        $municipios = $consultorio->listarMunicipios($id_estado);
        echo json_encode($municipios);
        exit();
    }

    // Listar parroquias por municipio
    if ($funcion == 'listar_parroquias') {
        $id_municipio = $_POST['id_municipio'];
        $parroquias = $consultorio->listarParroquias($id_municipio);
        echo json_encode($parroquias);
        exit();
    }
}

echo json_encode(['error' => 'Función no válida']);
?>
