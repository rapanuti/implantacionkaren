<?php
session_start();
if($_SESSION['us_tipo'] == 2 && $_SESSION['rol'] == 'medico'){
    include_once '../layauts/header.php';
?>
<title>Médico | Catálogo</title>
<?php include_once '../layauts/nav_medico.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Panel del Médico</h1>
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
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="total_recetas">0</h3>
                            <p>Recetas Creadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </div>
                        <a href="../adm_recetas.php" class="small-box-footer">Ver recetas <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3 id="total_pacientes">0</h3>
                            <p>Pacientes Atendidos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="med_pacientes.php" class="small-box-footer">Ver pacientes <i class="fas fa-arrow-circle-right"></i></a>
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
                            <h4>Bienvenido, Dr(a). <?php echo htmlspecialchars($_SESSION['nombre_us'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p>Rol: Médico</p>
                            <p>Desde este panel puedes gestionar tus recetas médicas y ver tus pacientes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    cargarEstadisticas();
    
    function cargarEstadisticas() {
        $.ajax({
            url: '../../controlador/MedicoController.php',
            type: 'POST',
            data: { funcion: 'mis_estadisticas', id_medico: <?php echo (int)($_SESSION['usuario'] ?? 0); ?> },
            dataType: 'json',
            success: function(data) {
                $('#total_recetas').text(data.total_recetas || 0);
                $('#total_pacientes').text(data.total_pacientes || 0);
            },
            error: function() {
                $('#total_recetas').text('0');
                $('#total_pacientes').text('0');
            }
        });
    }
});
</script>

<?php
include_once '../layauts/footer.php';
}
else{
    header('Location: ../login_medico.php');
}
?>