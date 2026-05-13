<?php
session_start();
if($_SESSION['us_tipo'] == 4 && $_SESSION['rol'] == 'administrador'){
    include_once '../layauts/header.php';
?>
<title>Administrador | Usuarios</title>
<?php include_once '../layauts/nav_administrador.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Usuarios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="adm_catalogo.php">Home</a></li>
                        <li class="breadcrumb-item active">Usuarios</li>
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
                            <h3 class="card-title">Listado de Usuarios</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>Cédula</th>
                                        <th>Tipo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_usuarios">
                                    <tr><td colspan="6" class="text-center">Cargando usuarios...</td</tr>
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
    cargarUsuarios();
    
    function cargarUsuarios() {
        $('#tabla_usuarios').html('<tr><td colspan="6" class="text-center">Cargando usuarios...</td></tr>');
        
        $.ajax({
            url: '../../controlador/AdministradorController.php',
            type: 'POST',
            data: { funcion: 'listar_usuarios' },
            dataType: 'json',
            success: function(usuarios) {
                let html = '';
                if (usuarios.length === 0) {
                    html = '<tr><td colspan="6" class="text-center">No hay usuarios registrados</td></tr>';
                } else {
                    for (let user of usuarios) {
                        html += `
                            <tr>
                                <td>${user.id}</td>
                                <td>${user.nombre}</td>
                                <td>${user.apellidos}</td>
                                <td>${user.cedula}</td>
                                <td><span class="badge badge-primary">${user.tipo}</span></td>
                                <td>
                                    <button class="btn btn-warning btn-sm btn-editar" data-id="${user.id}">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="${user.id}">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        `;
                    }
                }
                $('#tabla_usuarios').html(html);
            },
            error: function(xhr) {
                $('#tabla_usuarios').html('<tr><td colspan="6" class="text-center text-danger">Error al cargar usuarios</td></tr>');
            }
        });
    }
});
</script>

<?php
include_once '../layauts/footer.php';
}
else{
    header('Location: ../login_administrador.php');
}
?>
