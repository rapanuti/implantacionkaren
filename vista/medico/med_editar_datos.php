<?php
session_start();
if($_SESSION['us_tipo'] == 2 && $_SESSION['rol'] == 'medico'){
    include_once '../layauts/header.php';
?>
<title>Médico | Editar datos</title>
<?php include_once '../layauts/nav_medico.php'; ?>
<?php 
$id_medico = $_SESSION['usuario'];
echo "<!-- DEBUG: ID Médico = " . $id_medico . " -->"; 
?>

<!-- Modal Cambiar Contraseña -->
<div class="modal fade" id="cambiocontra" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cambiar contraseña</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="text-center">
          <img id="avatar3" src="../../img/avatar.png" class="profile-user-img img-fluid img-circle">
          <b><?php echo htmlspecialchars($_SESSION['nombre_us'] ?? '', ENT_QUOTES, 'UTF-8'); ?></b>
        </div>
        <div class="alert alert-success text-center" id="update" style="display:none;">Contraseña actualizada correctamente</div>
        <div class="alert alert-danger text-center" id="noupdate" style="display:none;">Contraseña actual incorrecta</div>
        <form id="form-pass">
          <input id="oldpass" type="password" class="form-control mb-2" placeholder="Contraseña actual">
          <input id="newpass" type="text" class="form-control" placeholder="Contraseña nueva">
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Cambiar Avatar -->
<div class="modal fade" id="cambiophoto" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cambiar avatar</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="text-center">
        <img id="avatar3" src="../../img/avatarDES.jpg" class="profile-user-img img-fluid img-circle">
          <b><?php echo htmlspecialchars($_SESSION['nombre_us'] ?? '', ENT_QUOTES, 'UTF-8'); ?></b>
        </div>
        <div class="alert alert-success text-center" id="edit" style="display:none;">Avatar actualizado correctamente</div>
        <div class="alert alert-danger text-center" id="noedit" style="display:none;">Formato no admitido</div>
        <form id="form-photo">
          <input type="file" name="photo" class="form-control">
          <input type="hidden" name="funcion" value="cambiar_foto">
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Contenido principal-->
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Datos personales</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="med_catalogo.php">Home</a></li>
            <li class="breadcrumb-item active">Datos personales</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section>
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                 <img id="avatar2" src="../../img/avatarDES.jpg" class="profile-user-img img-fluid img-circle">
                </div>
                <div class='text-center mt-1'>
                  <button type='button' data-toggle="modal" data-target="#cambiophoto" class='btn btn-primary btn-sm'>Cambiar avatar</button>
                </div>
                <input id="id_usuario" type="hidden" value="<?php echo (int)($_SESSION['usuario'] ?? 0); ?>">
                <h3 id="nombre_us" class="profile-username text-center text-success">NOMBRE</h3>
                <p id="apellidos_us" class="text-muted text-center">Apellido</p>
                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b style="color:#0b7300">Edad</b>
                    <a id="edad" class="float-right">12</a>
                  </li>
                  <li class="list-group-item">
                    <b style="color:#0b7300">Cédula</b>
                    <a id="cedula_us" class="float-right">12</a>
                  </li>
                  <li class="list-group-item">
                    <b style="color:#0b7300">Tipo Usuario</b>
                    <span id="us_tipo" class="float-right badge badge-primary">Médico</span>
                  </li>
                  <button data-toggle="modal" data-target="#cambiocontra" type="button" class="btn btn-block btn-outline-warning btn-sm">Cambiar contraseña</button>
                </ul>
              </div>
            </div>

            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Sobre mi</h3>
              </div>
              <div class="card-body">
                <strong style="color:#0b7300"><i class="fas fa-phone mr-1"></i>Teléfono</strong>
                <p id="telefono_us" class="text-muted">-</p>
                <strong style="color:#0b7300"><i class="fas fa-map-marker-alt mr-1"></i>Dirección</strong>
                <p id="direccion_us" class="text-muted">-</p>
                <strong style="color:#0b7300"><i class="fas fa-at mr-1"></i>Correo</strong>
                <p id="correo_us" class="text-muted">-</p>
                <strong style="color:#0b7300"><i class="fas fa-smile-wink mr-1"></i>Sexo</strong>
                <p id="sexo_us" class="text-muted">-</p>
                <strong style="color:#0b7300"><i class="fas fa-pencil-alt mr-1"></i>Información adicional</strong>
                <p id="adicional_us" class="text-muted">-</p>
                <button class="edit btn btn-block bg-gradient-danger">Editar</button>
              </div>
              <div class="card-footer">
                <p class="text-muted">Click en el botón si desea editar</p>
              </div>
            </div>
          </div>

          <div class="col-md-9">
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Editar datos personales</h3>
              </div>
              <div class="card-body">
                <div class="alert alert-success text-center" id="editado" style="display:none;">
                  <span><i class="fas fa-check m-1"></i>Editado</span>
                </div>
                <div class="alert alert-danger text-center" id="noeditado" style="display:none;">
                  <span><i class="fas fa-times m-1"></i>Edición deshabilitada</span>
                </div>
                <form id="form-usuario" class="form-horizontal">
                  <div class="form-group row">
                    <label for="telefono" class="col-sm-2 col-form-label">Teléfono</label>
                    <div class="col-sm-10">
                      <input type="number" id="telefono" class="form-control">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="direccion" class="col-sm-2 col-form-label">Dirección</label>
                    <div class="col-sm-10">
                      <input type="text" id="direccion" class="form-control">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="correo" class="col-sm-2 col-form-label">Correo</label>
                    <div class="col-sm-10">
                      <input type="text" id="correo" class="form-control">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="sexo" class="col-sm-2 col-form-label">Sexo</label>
                    <div class="col-sm-10">
                      <input type="text" id="sexo" class="form-control">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="adicional" class="col-sm-2 col-form-label">Información adicional</label>
                    <div class="col-sm-10">
                      <textarea class="form-control" id="adicional" rows="5"></textarea>
                    </div>
                  </div>
                  <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10 float-right">
                      <button class="btn btn-block btn-outline-success">Guardar</button>
                    </div>
                  </div>
                </form>
              </div>
              <div class="card-footer">
                <p class="text-muted">Cuidado con ingresar datos erróneos</p>
              </div>
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
    header('Location: ../login_medico.php');
}
?>
<script src="../../js/medico.js"></script>
