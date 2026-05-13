<?php
session_start();
if($_SESSION['us_tipo'] == 1 && $_SESSION['rol'] == 'paciente'){
    include_once '../layauts/header.php';
?>
<title>Paciente | Mis Recetas</title>
<?php include_once '../layauts/nav_paciente.php'; ?>

<style>
    .table-actions {
        white-space: nowrap;
        width: 60px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-prescription-bottle-alt"></i> Mis Recetas Médicas</h1>
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
                            <h3 class="card-title">Listado de Recetas</h3>
                            <div class="card-tools">
                                <div class="input-group input-group-sm" style="width: 200px;">
                                    <input type="text" id="buscar_receta" class="form-control float-right" placeholder="Buscar...">
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
                                        <th>Médico</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_recetas">
                                    <tr><td colspan="7" class="text-center">Cargando recetas...</td</tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(document).ready(function() {
    listar_recetas();

    $('#buscar_receta').on('keyup', function() {
        let value = $(this).val().toLowerCase();
        $('#tabla_recetas tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    function listar_recetas() {
        $('#tabla_recetas').html('<tr><td colspan="7" class="text-center">Cargando recetas...<div class="spinner-border spinner-border-sm ml-2"></div></td></tr>');
        
        $.ajax({
            url: '../../controlador/RecetaController.php',
            type: 'POST',
            data: { funcion: 'mis_recetas', id_paciente: <?php echo (int)($_SESSION['usuario'] ?? 0); ?> },
            dataType: 'json',
            success: function(recetas) {
                let html = '';
                if (!recetas || recetas.length === 0) {
                    html = '<tr><td colspan="7" class="text-center">No hay recetas registradas</td></tr>';
                } else {
                    for (let receta of recetas) {
                        html += `
                            <tr>
                                <td>${receta.id_receta}</td>
                                <td><strong>${receta.nombre_medicamento}</strong></td>
                                <td>${receta.marca}</td>
                                <td>${receta.cantidad}</td>
                                <td>${receta.dosis || '-'}</td>
                                <td>${receta.medico || 'N/A'}</td>
                                <td>${receta.fecha_receta}</td>
                            </tr>
                        `;
                    }
                }
                $('#tabla_recetas').html(html);
            },
            error: function() {
                $('#tabla_recetas').html('<tr><td colspan="7" class="text-center text-danger">Error al cargar recetas</td></tr>');
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
