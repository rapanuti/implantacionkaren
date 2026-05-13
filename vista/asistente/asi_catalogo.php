<?php
session_start();
if($_SESSION['us_tipo'] == 3 && $_SESSION['rol'] == 'asistente'){
    include_once '../layauts/header.php';
?>
<title>Asistente | Catálogo</title>
<?php include_once '../layauts/nav_asistente.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Bienvenido Asistente</h1>
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
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Panel de Control</h3>
                        </div>
                        <div class="card-body">
                            <h4>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_us'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p>Rol: Asistente</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
include_once '../layauts/footer.php';
}
else{
    header('Location: ../login_asistente.php');
}
?>
