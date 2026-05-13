<?php
session_start();
if($_SESSION['us_tipo'] == 4 && $_SESSION['rol'] == 'administrador'){
    include_once '../layauts/header.php';
?>
<title>Administrador | Detalle Consultorio</title>
<?php include_once '../layauts/nav_administrador.php'; ?>

<style>
    .info-box-icon-custom {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 15px;
    }
    .medico-item {
        border-left: 3px solid #17a2b8;
        margin-bottom: 10px;
        transition: all 0.2s;
    }
    .medico-item:hover {
        background-color: #f8f9fa;
    }
    .especialidad-badge {
        display: inline-block;
        background: #e9ecef;
        padding: 5px 12px;
        border-radius: 20px;
        margin: 3px;
        font-size: 12px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-building"></i> <span id="consultorio_nombre">Detalle del Consultorio</span></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="adm_catalogo.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="adm_consultorios.php">Consultorios</a></li>
                        <li class="breadcrumb-item active">Detalle</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <input type="hidden" id="id_consultorio" value="<?php echo $_GET['id'] ?? ''; ?>">
            
            <div class="row">
                <!-- Columna izquierda - Información Principal -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <div class="info-box-icon-custom bg-primary mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%;">
                                    <i class="fas fa-hospital-user fa-3x text-white"></i>
                                </div>
                            </div>
                            <h3 class="profile-username text-center" id="detalle_nombre">-</h3>
                            <p class="text-muted text-center" id="detalle_ciudad">-</p>
                            
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b><i class="fas fa-clock"></i> Horario</b>
                                    <a class="float-right" id="detalle_horario">-</a>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-phone"></i> Teléfono</b>
                                    <a class="float-right" id="detalle_telefono">-</a>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-envelope"></i> Email</b>
                                    <a class="float-right" id="detalle_email">-</a>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-map-marker-alt"></i> Dirección</b>
                                    <a class="float-right" id="detalle_direccion">-</a>
                                </li>
                            </ul>
                            
                            <div class="text-center">
                                <a href="adm_consultorio_horarios.php?id=<?php echo $_GET['id'] ?? ''; ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-calendar-alt"></i> Gestionar Horarios
                                </a>
                                <a href="adm_consultorio_editar.php?id=<?php echo $_GET['id'] ?? ''; ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Editar Información
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Especialidades -->
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-stethoscope"></i> Especialidades Admitidas</h3>
                        </div>
                        <div class="card-body" id="contenedor_especialidades">
                            <p class="text-muted text-center">Cargando...</p>
                        </div>
                    </div>

                    <!-- Citas Históricas -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-line"></i> Estadísticas</h3>
                        </div>
                        <div class="card-body text-center">
                            <h2 class="text-info" id="total_citas">0</h2>
                            <p>Citas Históricas</p>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha - Médicos Asignados -->
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-md"></i> Médicos Asignados</h3>
                            <div class="card-tools">
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalAsignarMedico">
                                    <i class="fas fa-plus"></i> Asignar Médico
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="contenedor_medicos">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm"></div> Cargando...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Información Adicional</h3>
                        </div>
                        <div class="card-body" id="detalle_descripcion">
                            <p class="text-muted">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Asignar Médico -->
<div class="modal fade" id="modalAsignarMedico" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Asignar Médico</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Seleccionar Médico</label>
                    <select class="form-control" id="medico_seleccionado">
                        <option value="">Seleccione un médico...</option>
                    </select>
                </div>
                <div id="mensaje_asignacion" class="alert" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnAsignarMedico">Asignar</button>
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
