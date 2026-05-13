<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-02, PROBLEMA-04
 *   Reemplazada verificación manual de sesión por requireAuth() del middleware.
 *   Agregada validación CSRF en todas las peticiones POST.
 */
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

include_once '../modelo/Receta.php';
require_once 'auth_middleware.php';

requireAuth();   // C5: Verifica sesión activa (médico, asistente o admin)
validateCsrf();  // D4: Valida CSRF

$funcion = isset($_POST['funcion']) ? $_POST['funcion'] : '';

if ($funcion == 'listar_recetas') {
    $id_medico = ($_SESSION['us_tipo'] == 2) ? $_SESSION['usuario'] : null;
    $resultados = $receta->obtener_recetas($id_medico);
    $json = array();
    
    if(!empty($resultados)) {
        foreach ($resultados as $objeto) {
            $json[] = array(
                'id_receta' => $objeto->id_receta,
                'nombre_medicamento' => $objeto->nombre_medicamento,
                'marca' => $objeto->marca,
                'cantidad' => $objeto->cantidad,
                'dosis' => isset($objeto->dosis) ? $objeto->dosis : '',
                'instrucciones' => isset($objeto->instrucciones) ? $objeto->instrucciones : '',
                'paciente' => isset($objeto->nombre_paciente) ? $objeto->nombre_paciente : 'N/A',
                'medico' => isset($objeto->nombre_medico) ? $objeto->nombre_medico : 'N/A',
                'fecha_receta' => $objeto->fecha_receta
            );
        }
    }
    echo json_encode($json);
    exit();
}

if ($funcion == 'crear_receta') {
    $nombre_medicamento = isset($_POST['nombre_medicamento']) ? $_POST['nombre_medicamento'] : '';
    $marca = isset($_POST['marca']) ? $_POST['marca'] : '';
    $cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : '';
    $dosis = isset($_POST['dosis']) ? $_POST['dosis'] : '';
    $instrucciones = isset($_POST['instrucciones']) ? $_POST['instrucciones'] : '';
    $id_paciente = isset($_POST['id_paciente']) ? $_POST['id_paciente'] : '';
    $id_medico = $_SESSION['usuario'];
    $fecha_receta = isset($_POST['fecha_receta']) ? $_POST['fecha_receta'] : '';
    
    // Validar campos requeridos
    if(empty($nombre_medicamento) || empty($marca) || empty($cantidad) || empty($id_paciente) || empty($fecha_receta)) {
        echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos requeridos']);
        exit();
    }
    
    // Llamar al método y capturar la salida
    ob_start();
    $receta->crear_receta($nombre_medicamento, $marca, $cantidad, $dosis, $instrucciones, $id_paciente, $id_medico, $fecha_receta);
    $output = ob_get_clean();
    
    // Verificar el resultado
    if(trim($output) == 'creado') {
        echo json_encode(['success' => true, 'message' => 'Receta creada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la receta: ' . $output]);
    }
    exit();
}

if ($funcion == 'editar_receta') {
    $id_receta = isset($_POST['id_receta']) ? $_POST['id_receta'] : '';
    $nombre_medicamento = isset($_POST['nombre_medicamento']) ? $_POST['nombre_medicamento'] : '';
    $marca = isset($_POST['marca']) ? $_POST['marca'] : '';
    $cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : '';
    $dosis = isset($_POST['dosis']) ? $_POST['dosis'] : '';
    $instrucciones = isset($_POST['instrucciones']) ? $_POST['instrucciones'] : '';
    $id_paciente = isset($_POST['id_paciente']) ? $_POST['id_paciente'] : '';
    $fecha_receta = isset($_POST['fecha_receta']) ? $_POST['fecha_receta'] : '';
    
    if(empty($nombre_medicamento) || empty($marca) || empty($cantidad) || empty($id_paciente) || empty($fecha_receta)) {
        echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos requeridos']);
        exit();
    }
    
    ob_start();
    $receta->editar_receta($id_receta, $nombre_medicamento, $marca, $cantidad, $dosis, $instrucciones, $id_paciente, $fecha_receta);
    $output = ob_get_clean();
    
    if(trim($output) == 'editado') {
        echo json_encode(['success' => true, 'message' => 'Receta actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la receta: ' . $output]);
    }
    exit();
}

if ($funcion == 'borrar_receta') {
    $id_receta = isset($_POST['id_receta']) ? $_POST['id_receta'] : '';
    if(!empty($id_receta)) {
        ob_start();
        $receta->borrar_receta($id_receta);
        $output = ob_get_clean();
        
        if(trim($output) == 'borrado') {
            echo json_encode(['success' => true, 'message' => 'Receta borrada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al borrar la receta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID de receta no válido']);
    }
    exit();
}

if ($funcion == 'obtener_receta') {
    $id_receta = isset($_POST['id_receta']) ? $_POST['id_receta'] : '';
    $resultados = $receta->obtener_receta($id_receta);
    $json = array();
    
    if(!empty($resultados)) {
        foreach ($resultados as $objeto) {
            $json = array(
                'id_receta' => $objeto->id_receta,
                'nombre_medicamento' => $objeto->nombre_medicamento,
                'marca' => $objeto->marca,
                'cantidad' => $objeto->cantidad,
                'dosis' => isset($objeto->dosis) ? $objeto->dosis : '',
                'instrucciones' => isset($objeto->instrucciones) ? $objeto->instrucciones : '',
                'id_paciente' => $objeto->id_paciente,
                'fecha_receta' => $objeto->fecha_receta
            );
        }
    }
    echo json_encode($json);
    exit();
}

if ($funcion == 'buscar_pacientes') {
    $dato = isset($_POST['dato']) ? $_POST['dato'] : '';
    $resultados = $receta->buscar_pacientes($dato);
    $json = array();
    
    if(!empty($resultados)) {
        foreach ($resultados as $objeto) {
            $json[] = array(
                'id_usuario' => $objeto->id_usuario,
                'nombre_completo' => $objeto->nombre_us . ' ' . $objeto->apellidos_us,
                'cedula' => $objeto->cedula_us,
                'fecha_nacimiento' => $objeto->edad,  
                'sexo' => $objeto->sexo_us  
            );
        }
    }
    echo json_encode($json);
    exit();
}

// Guardar diagnóstico
if ($funcion == 'guardar_diagnostico') {
    $id_receta = isset($_POST['id_receta']) ? $_POST['id_receta'] : '';
    $diagnostico = isset($_POST['diagnostico']) ? $_POST['diagnostico'] : '';
    $trat_sugerido = isset($_POST['trat_sugerido']) ? $_POST['trat_sugerido'] : '';
    
    if(empty($id_receta) || empty($diagnostico)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit();
    }
    
    $resultado = $receta->guardarDiagnostico($id_receta, $diagnostico, $trat_sugerido);
    echo json_encode($resultado);
    exit();
}

// Guardar estudio de laboratorio
if ($funcion == 'guardar_estudio_lab') {
    $id_receta = isset($_POST['id_receta']) ? $_POST['id_receta'] : '';
    $est_solicitado = isset($_POST['est_solicitado']) ? $_POST['est_solicitado'] : '';
    $obs_adicional = isset($_POST['obs_adicional']) ? $_POST['obs_adicional'] : '';
    
    if(empty($id_receta) || empty($est_solicitado)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit();
    }
    
    $resultado = $receta->guardarEstudioLab($id_receta, $est_solicitado, $obs_adicional);
    echo json_encode($resultado);
    exit();
}

// Obtener diagnóstico por ID de receta
if ($funcion == 'obtener_diagnostico') {
    $id_receta = isset($_POST['id_receta']) ? $_POST['id_receta'] : '';
    $resultado = $receta->obtenerDiagnostico($id_receta);
    echo json_encode($resultado);
    exit();
}

// Obtener estudio por ID de receta
if ($funcion == 'obtener_estudio_lab') {
    $id_receta = isset($_POST['id_receta']) ? $_POST['id_receta'] : '';
    $resultado = $receta->obtenerEstudioLab($id_receta);
    echo json_encode($resultado);
    exit();
}
if ($funcion == 'mis_recetas') {
    $id_paciente = isset($_POST['id_paciente']) ? $_POST['id_paciente'] : '';
    $resultados = $receta->obtenerRecetasPorPaciente($id_paciente);
    $json = array();
    
    if(!empty($resultados)) {
        foreach ($resultados as $objeto) {
            $json[] = array(
                'id_receta' => $objeto->id_receta,
                'nombre_medicamento' => $objeto->nombre_medicamento,
                'marca' => $objeto->marca,
                'cantidad' => $objeto->cantidad,
                'dosis' => isset($objeto->dosis) ? $objeto->dosis : '',
                'instrucciones' => isset($objeto->instrucciones) ? $objeto->instrucciones : '',
                'medico' => isset($objeto->nombre_medico) ? $objeto->nombre_medico : 'N/A',
                'fecha_receta' => $objeto->fecha_receta
            );
        }
    }
    echo json_encode($json);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Función no válida: ' . $funcion]);
?>