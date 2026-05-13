<?php
session_start();
if($_SESSION['us_tipo'] == 2 && $_SESSION['rol'] == 'medico'){
    include_once '../layauts/header.php';
?>
<title>Médico | Mis Pacientes</title>
<?php include_once '../layauts/nav_medico.php'; ?>

<style>
    .table-actions {
        white-space: nowrap;
        width: 80px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-users"></i> Mis Pacientes</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="med_catalogo.php">Home</a></li>
                        <li class="breadcrumb-item active">Pacientes</li>
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
                            <h3 class="card-title">Listado de Pacientes Atendidos</h3>
                            <div class="card-tools">
                                <div class="input-group input-group-sm" style="width: 200px;">
                                    <input type="text" id="buscar_paciente" class="form-control float-right" placeholder="Buscar...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>Cédula</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                            <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_pacientes">
                                    <tr><td colspan="7" class="text-center">Cargando pacientes...</td</tr>
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
    listar_pacientes();

    $('#buscar_paciente').on('keyup', function() {
        let value = $(this).val().toLowerCase();
        $('#tabla_pacientes tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    function listar_pacientes() {
        $('#tabla_pacientes').html('<tr><td colspan="7" class="text-center">Cargando pacientes...<div class="spinner-border spinner-border-sm ml-2"></div></td></tr>');
        
        $.ajax({
            url: '../../controlador/MedicoController.php',
            type: 'POST',
            data: { funcion: 'listar_pacientes', id_medico: <?php echo (int)($_SESSION['usuario'] ?? 0); ?> },
            dataType: 'json',
            success: function(pacientes) {
                let html = '';
                if (!pacientes || pacientes.length === 0) {
                    html = '<tr><td colspan="7" class="text-center">No hay pacientes registrados</td</tr>';
                } else {
                    for (let paciente of pacientes) {
                        html += `
                            <tr>
                                <td>${paciente.id_paciente}</td>
                                <td><strong>${paciente.nombre}</strong></td>
                                <td>${paciente.apellidos}</td>
                                <td>${paciente.cedula}</td>
                                <td>${paciente.telefono || '-'}</td>
                                <td>${paciente.correo || '-'}</td>
                                <td class="table-actions">
                                    <button class="btn btn-info btn-sm btn-ver-recetas" data-id="${paciente.id_paciente}" data-nombre="${paciente.nombre} ${paciente.apellidos}">
                                        <i class="fas fa-prescription-bottle-alt"></i> Recetas
                                    </button>
                                 </td>
                            </tr>
                        `;
                    }
                }
                $('#tabla_pacientes').html(html);
                
                // Evento para ver recetas del paciente
                $('.btn-ver-recetas').click(function() {
                    let id_paciente = $(this).data('id');
                    let nombre_paciente = $(this).data('nombre');
                    verRecetasPaciente(id_paciente, nombre_paciente);
                });
            },
            error: function() {
                $('#tabla_pacientes').html('<tr><td colspan="7" class="text-center text-danger">Error al cargar pacientes</td</tr>');
            }
        });
    }
    
    function verRecetasPaciente(id_paciente, nombre_paciente) {
        // Redirigir a la página de recetas con el paciente seleccionado
        window.location.href = '../adm_recetas.php?paciente_id=' + id_paciente + '&paciente_nombre=' + encodeURIComponent(nombre_paciente);
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
