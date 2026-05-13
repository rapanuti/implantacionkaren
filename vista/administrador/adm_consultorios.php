<?php
session_start();
if($_SESSION['us_tipo'] == 4 && $_SESSION['rol'] == 'administrador'){
    include_once '../layauts/header.php';
?>
<title>Administrador | Consultorios</title>
<?php include_once '../layauts/nav_administrador.php'; ?>

<style>
    .consultorio-card {
        transition: transform 0.2s;
        margin-bottom: 20px;
    }
    .consultorio-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .badge-medicos {
        background-color: #17a2b8;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
    }
    .especialidad-tag {
        display: inline-block;
        background-color: #e9ecef;
        padding: 3px 8px;
        border-radius: 15px;
        font-size: 11px;
        margin: 2px;
    }
    .search-box {
        max-width: 300px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-building"></i> Gestión de Consultorios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="adm_catalogo.php">Home</a></li>
                        <li class="breadcrumb-item active">Consultorios</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
          <!-- Estadísticas y acciones en una sola fila -->
<div class="row">
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-building"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Consultorios</span>
                <span class="info-box-number" id="total_consultorios">0</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Activos</span>
                <span class="info-box-number" id="total_activos">0</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ocupación</span>
                <span class="info-box-number" id="tasa_ocupacion">0%</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-plus-circle"></i></span>
            <div class="info-box-content">
                <button class="btn btn-primary btn-sm btn-block" id="btnNuevoConsultorio">
                    <i class="fas fa-plus"></i> Nuevo Consultorio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Barra de búsqueda -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-2">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" id="buscar_consultorio" class="form-control" placeholder="Buscar consultorio por nombre, ciudad o dirección...">
                    <div class="input-group-append">
                        <button class="btn btn-secondary" id="btnBuscar">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <button class="btn btn-outline-secondary" id="btnLimpiarBusqueda" style="display: none;">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                    </div>
                </div>
                <div id="resultado_busqueda" class="mt-2 text-center" style="display: none;">
                    <small class="text-muted">
                        <i class="fas fa-filter"></i> Mostrando resultados para: 
                        <strong id="termino_busqueda" class="text-primary"></strong>
                        <a href="#" id="limpiarResultados" class="ml-2 text-danger">
                            <i class="fas fa-times-circle"></i> quitar filtro
                        </a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
            <!-- Tarjetas de consultorios -->
            <div class="row" id="contenedor_consultorios">
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p>Cargando consultorios...</p>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar este consultorio?</p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
                <input type="hidden" id="eliminar_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarEliminar">Eliminar</button>
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
