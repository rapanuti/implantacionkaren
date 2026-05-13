<?php
session_start();
if($_SESSION['us_tipo'] == 1 && $_SESSION['rol'] == 'paciente'){
    include_once '../layauts/header.php';
?>
<title>Paciente | Catálogo</title>
<?php include_once '../../vista/layauts/nav_paciente.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Panel del Paciente</h1>
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
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="total_recetas">0</h3>
                            <p>Mis Recetas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </div>
                        <a href="pac_recetas.php" class="small-box-footer">Ver recetas <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3 id="proximas_citas">0</h3>
                            <p>Próximas Citas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <a href="#" class="small-box-footer">Ver citas <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Bienvenido</h3>
                        </div>
                        <div class="card-body">
                            <h4>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_us'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p>Rol: Paciente</p>
                            <p>Desde este panel puedes ver tus recetas médicas y gestionar tus citas.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // Cargar estadísticas del paciente
    cargarEstadisticas();
    
    function cargarEstadisticas() {
        $.ajax({
            url: '../../controlador/PacienteController.php',
            type: 'POST',
            data: { funcion: 'mis_estadisticas', id_paciente: <?php echo (int)($_SESSION['usuario'] ?? 0); ?> },
            dataType: 'json',
            success: function(data) {
                $('#total_recetas').text(data.total_recetas || 0);
                $('#proximas_citas').text(data.proximas_citas || 0);
            },
            error: function() {
                $('#total_recetas').text('0');
                $('#proximas_citas').text('0');
            }
        });
    }
});
</script>

<?php
include_once '../layauts/footer.php';
}
else{
    header('Location: ../login_paciente.php');
}
?>