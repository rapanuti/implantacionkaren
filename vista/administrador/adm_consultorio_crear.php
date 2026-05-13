<?php
session_start();
if($_SESSION['us_tipo'] == 4 && $_SESSION['rol'] == 'administrador'){
    include_once '../layauts/header.php';
?>
<title>Administrador | Nuevo Consultorio</title>
<?php include_once '../layauts/nav_administrador.php'; ?>

<style>
    .checkbox-group {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 10px;
    }
    .checkbox-group .form-check {
        margin-bottom: 5px;
    }
    .preview-card {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-plus-circle"></i> Crear Consultorio</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="adm_catalogo.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="adm_consultorios.php">Consultorios</a></li>
                        <li class="breadcrumb-item active">Crear</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Información Básica</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success" id="alertExito" style="display:none;">
                                <i class="fas fa-check-circle"></i> Consultorio creado exitosamente
                            </div>
                            <div class="alert alert-danger" id="alertError" style="display:none;">
                                <i class="fas fa-exclamation-circle"></i> <span id="errorMensaje"></span>
                            </div>
                            
                            <form id="formCrearConsultorio">
                                <div class="form-group">
                                    <label>Nombre del Consultorio <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Apertura Habitual <span class="text-danger">*</span></label>
                                            <input type="time" class="form-control" id="apertura" name="apertura" value="08:00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Cierre Habitual <span class="text-danger">*</span></label>
                                            <input type="time" class="form-control" id="cierre" name="cierre" value="17:00" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Especialidades Admitidas</label>
                                    <div class="checkbox-group" id="especialidades_container">
                                        <div class="text-center">Cargando especialidades...</div>
                                    </div>
                                </div>

                              <!-- Ubicación -->
<h4 class="mt-4"><i class="fas fa-map-marker-alt"></i> Ubicación</h4>
<hr>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Estado <span class="text-danger">*</span></label>
            <select class="form-control" id="estado" name="estado" required>
                <option value="">Seleccione un estado...</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Ciudad <span class="text-danger">*</span></label>
            <select class="form-control" id="ciudad" name="ciudad" required disabled>
                <option value="">Primero seleccione un estado...</option>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Municipio</label>
            <select class="form-control" id="municipio" name="municipio" disabled>
                <option value="">Primero seleccione una ciudad...</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Parroquia</label>
            <select class="form-control" id="parroquia" name="parroquia" disabled>
                <option value="">Primero seleccione un municipio...</option>
            </select>
        </div>
    </div>
</div>

<div class="form-group">
    <label>Dirección Detallada <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="direccion" name="direccion" required placeholder="Av. Principal, Edificio, Número, etc.">
</div>

                                
                             
                                <h4 class="mt-4"><i class="fas fa-address-card"></i> Datos de Contacto</h4>
                                <hr>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Teléfono Interno/Directo</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email del Consultorio</label>
                                            <input type="email" class="form-control" id="email" name="email">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg btn-block">
                                    <i class="fas fa-save"></i> Guardar Consultorio
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Preview -->
                <div class="col-md-4">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-eye"></i> Vista Previa</h3>
                        </div>
                        <div class="card-body">
                            <div class="preview-card p-3">
                                <h4 id="preview_nombre">Nombre del Consultorio</h4>
                                <p><i class="fas fa-map-marker-alt"></i> <span id="preview_ciudad">Ciudad</span></p>
                                <p id="preview_descripcion" class="text-muted small">Descripción</p>
                                <p><i class="fas fa-phone"></i> <span id="preview_telefono">-</span></p>
                                <p><i class="fas fa-envelope"></i> <span id="preview_email">-</span></p>
                                <hr>
                                <div class="text-center">
                                    <span class="badge badge-success">Consultorio disponible</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
