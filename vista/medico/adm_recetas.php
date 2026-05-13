<?php
session_start();
if($_SESSION['us_tipo'] == 2 && $_SESSION['rol'] == 'medico'){
    include_once '../layauts/header.php';
?>

<title>BioVital | Recetas</title>
<?php
if($_SESSION['us_tipo'] == 2){
    include_once '../layauts/nav_medico.php';
} else {
   include_once '../layauts/nav_paciente.php';
}
?>

<style>
    .table-actions {
        white-space: nowrap;
        width: 100px;
    }
    .btn-action {
        padding: 5px 10px;
        margin: 0 2px;
    }
    .modal-lg-custom {
        max-width: 800px;
    }
</style>

<!-- Modal para Crear/Editar Receta -->
<div class="modal fade" id="modalReceta" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg-custom" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalTitle">Nueva Receta</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id_receta" value="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre del Medicamento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre_medicamento" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Marca <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="marca" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cantidad <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cantidad" placeholder="Ej: 30 tabletas" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Dosis</label>
                            <input type="text" class="form-control" id="dosis" placeholder="Ej: 1 tableta cada 8 horas">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Paciente <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="buscar_paciente" placeholder="Buscar por cédula o nombre">
                            <input type="hidden" id="id_paciente">
                            <div id="resultados_pacientes" class="list-group mt-1" style="display:none; position:absolute; z-index:1000; width:95%;"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fecha de Receta <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_receta" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Instrucciones</label>
                    <textarea class="form-control" id="instrucciones" rows="3" placeholder="Instrucciones adicionales para el paciente..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardar">Guardar Receta</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Estudio Laboratorio -->
<div class="modal fade" id="modalEstudioLaboratorio" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-flask"></i> Estudio Laboratorio</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Buscar paciente -->
                <div class="form-group">
                    <label>Buscar Paciente por Cédula</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="buscar_paciente_lab" placeholder="Ingrese cédula del paciente">
                        <div class="input-group-append">
                            <button class="btn btn-info" type="button" id="btnBuscarPacienteLab">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <input type="hidden" id="id_paciente_lab">
                    <div id="info_paciente_lab" class="mt-3" style="display:none;">
                        <div class="card card-info">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Información del Paciente</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Nombre:</strong> <span id="lab_nombre"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Edad:</strong> <span id="lab_edad"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Sexo:</strong> <span id="lab_sexo"></span>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <strong>Médico:</strong> <span id="lab_medico"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estudios de laboratorio -->
                <div class="form-group mt-4">
                    <label><strong>Seleccione los estudios solicitados:</strong></label>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Hemograma" id="estudio_hemograma">
                                <label class="form-check-label" for="estudio_hemograma">
                                    Hemograma
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Coprocultivo" id="estudio_coprocultivo">
                                <label class="form-check-label" for="estudio_coprocultivo">
                                    Coprocultivo
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Placa de tórax" id="estudio_placa">
                                <label class="form-check-label" for="estudio_placa">
                                    Placa de tórax
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Examen de orina" id="estudio_orina">
                                <label class="form-check-label" for="estudio_orina">
                                    Examen de orina
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Tomografía computarizada" id="estudio_tomografia">
                                <label class="form-check-label" for="estudio_tomografia">
                                    Tomografía computarizada
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observaciones adicionales -->
                <div class="form-group mt-3">
                    <label>Observaciones adicionales</label>
                    <textarea class="form-control" id="lab_observaciones" rows="3" placeholder="Instrucciones adicionales para el paciente..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="btnGenerarRecetaLab">
                    <i class="fas fa-prescription-bottle-alt"></i> Generar Receta
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Modal para Diagnóstico -->
<div class="modal fade" id="modalDiagnostico" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-stethoscope"></i> Diagnóstico Médico</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Buscar paciente -->
                <div class="form-group">
                    <label>Buscar Paciente por Cédula</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="buscar_paciente_diag" placeholder="Ingrese cédula del paciente">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" id="btnBuscarPacienteDiag">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <input type="hidden" id="id_paciente_diag">
                    <div id="info_paciente_diag" class="mt-3" style="display:none;">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Información del Paciente</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Nombre:</strong> <span id="diag_nombre"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Edad:</strong> <span id="diag_edad"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Sexo:</strong> <span id="diag_sexo"></span>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <strong>Médico:</strong> <span id="diag_medico"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Diagnóstico -->
                <div class="form-group mt-4">
                    <label><strong>Diagnóstico</strong></label>
                    <textarea class="form-control" id="diagnostico_texto" rows="5" placeholder="Ingrese el diagnóstico del paciente..."></textarea>
                </div>

                <!-- Tratamiento sugerido -->
                <div class="form-group mt-3">
                    <label>Tratamiento sugerido (opcional)</label>
                    <textarea class="form-control" id="tratamiento_texto" rows="3" placeholder="Ingrese el tratamiento sugerido..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGenerarRecetaDiag">
                    <i class="fas fa-file-prescription"></i> Generar Receta
                </button>
            </div>
        </div>
    </div>
</div>



<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-prescription-bottle-alt"></i> Recetas Médicas</h1>
                </div>
               
            </div>
        </div>
    </section>
<div class="col-sm-6">
    <div class="btn-group">
        <button class="btn btn-info mr-2" id="btnEstudioLaboratorio">
            <i class="fas fa-flask"></i> Estudio Laboratorio
        </button>
        <button class="btn btn-primary mr-2" id="btnDiagnostico">
            <i class="fas fa-stethoscope"></i> Diagnóstico
        </button>
        <button class="btn btn-success" id="btnNuevaReceta">
            <i class="fas fa-plus"></i> Nueva Receta
        </button>
    </div>
</div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Listado de Recetas</h3>
                            <div class="card-tools">
                                <div class="input-group input-group-sm" style="width: 200px;">
                                    <input type="text" id="buscar_receta" class="form-control float-right" placeholder="Buscar...">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-default">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>    
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Medicamento</th>
                                        <th>Marca</th>
                                        <th>Cantidad</th>
                                        <th>Dosis</th>
                                        <th>Paciente</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_recetas">
                                    <tr>
                                        <td colspan="8" class="text-center">Cargando recetas...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Alertas -->
<div class="alert alert-success alert-dismissible fade show position-fixed" id="alertSuccess" style="top: 70px; right: 20px; z-index: 1050; display:none;">
    <i class="fas fa-check-circle"></i> <span id="successMessage"></span>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
<div class="alert alert-danger alert-dismissible fade show position-fixed" id="alertError" style="top: 70px; right: 20px; z-index: 1050; display:none;">
    <i class="fas fa-exclamation-circle"></i> <span id="errorMessage"></span>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>


<?php include_once '../layauts/footer.php'; ?>
<script>
    var BASE_URL = '<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>';
</script>
<script src="../../js/recetas.js"></script>

<?php
} else {
    header('Location:../../index.php');
}
?>