<?php
session_start();
if($_SESSION['us_tipo'] == 4 && $_SESSION['rol'] == 'administrador'){
    include_once '../layauts/header.php';
?>
<title>Administrador | Horarios Consultorio</title>
<?php include_once '../layauts/nav_administrador.php'; ?>

<style>
    .horario-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        height: 100%;
    }
    .horario-card h4 {
        border-bottom: 2px solid #007bff;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }
    .horario-slot {
        background: white;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .horario-slot.disponible {
        border-left: 4px solid #28a745;
    }
    .horario-slot.ocupado {
        border-left: 4px solid #dc3545;
    }
    .btn-horario {
        margin-top: 5px;
        padding: 2px 8px;
        font-size: 12px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-calendar-alt"></i> Ocupación del Consultorio</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="adm_catalogo.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="adm_consultorios.php">Consultorios</a></li>
                        <li class="breadcrumb-item"><a href="#" id="volver_detalle">Detalle</a></li>
                        <li class="breadcrumb-item active">Horarios</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <input type="hidden" id="id_consultorio" value="<?php echo $_GET['id'] ?? ''; ?>">
            
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Ocupación - <span id="consultorio_nombre">Cargando...</span></h3>
                            <div class="card-tools">
                                <button class="btn btn-light btn-sm" id="btnRefresh">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success" id="alertExito" style="display:none;">
                                <i class="fas fa-check-circle"></i> Horario guardado correctamente
                            </div>
                            <div class="alert alert-danger" id="alertError" style="display:none;">
                                <i class="fas fa-exclamation-circle"></i> <span id="errorMensaje"></span>
                            </div>
                            
                            <div class="row" id="contenedor_horarios">
                                <div class="col-12 text-center">
                                    <div class="spinner-border text-primary"></div>
                                    <p>Cargando horarios...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Editar Horario -->
<div class="modal fade" id="modalHorario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-clock"></i> Editar Horario</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="horario_dia">
                <input type="hidden" id="horario_turno">
                
                <div class="form-group">
                    <label>Día</label>
                    <input type="text" class="form-control" id="horario_dia_text" readonly>
                </div>
                
                <div class="form-group">
                    <label>Turno</label>
                    <input type="text" class="form-control" id="horario_turno_text" readonly>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Hora de Inicio</label>
                            <input type="time" class="form-control" id="hora_inicio" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Hora de Fin</label>
                            <input type="time" class="form-control" id="hora_fin" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Médico Asignado</label>
                    <select class="form-control" id="medico_asignado">
                        <option value="">Sin asignar</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarHorario">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="../../js/consultorio.js"></script>

<?php
include_once '../layauts/footer.php';
}
else{
    header('Location: ../login_administrador.php');
}
?>
