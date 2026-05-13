<?php
session_start();
if($_SESSION['us_tipo'] == 4 && $_SESSION['rol'] == 'administrador'){
    include_once '../layauts/header.php';
?>
<title>Administrador | Catálogo</title>
<?php include_once '../layauts/nav_administrador.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Panel de Administración</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Usuarios</span>
                            <span class="info-box-number" id="total_usuarios">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-prescription-bottle-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Recetas</span>
                            <span class="info-box-number" id="total_recetas">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-user-md"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Médicos</span>
                            <span class="info-box-number" id="total_medicos">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-user-friends"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pacientes</span>
                            <span class="info-box-number" id="total_pacientes">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Bienvenido Administrador</h3>
                        </div>
                        <div class="card-body">
                            <h4>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_us'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p>Rol: Administrador</p>
                            <p>Desde este panel puedes gestionar todos los aspectos del sistema.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Cargar estadísticas
$(document).ready(function() {
    // Aquí puedes agregar AJAX para cargar estadísticas reales
    $('#total_usuarios').text('4');
    $('#total_recetas').text('8');
    $('#total_medicos').text('1');
    $('#total_pacientes').text('2');
});
</script>

<?php
include_once '../layauts/footer.php';
}
else{
    header('Location: ../login_administrador.php');
}
?>
