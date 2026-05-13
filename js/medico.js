$(document).ready(function() {
    var id_usuario = $('#id_usuario').val();
    console.log('=== DEPURACIÓN MÉDICO ===');
    console.log('ID Usuario desde PHP:', id_usuario);
    console.log('Tipo de ID:', typeof id_usuario);
    console.log('jQuery cargado:', typeof $ !== 'undefined');
    
    if(!id_usuario || id_usuario === '') {
        console.error('ERROR: ID de médico no encontrado');
        $('#nombre_us').html('Error: Sesión no válida');
        return;
    }
    
    buscar_medico(id_usuario);

   function buscar_medico(dato) {
    funcion = 'buscar_medico';
    console.log('Buscando médico con dato:', dato);
    
    $.ajax({
        url: '../../controlador/MedicoController.php',
        type: 'POST',
        data: {dato: dato, funcion: funcion},
        dataType: 'json',
        success: function(medico) {
            console.log('Médico recibido:', medico);
            
            if(medico.error) {
                console.error('Error:', medico.error);
                $('#nombre_us').html('Error: ' + medico.error);
                return;
            }
            
            $('#nombre_us').html(medico.nombre || '');
            $('#apellidos_us').html(medico.apellidos || '');
            $('#edad').html(medico.fecha_nacimiento || '');
            $('#cedula_us').html(medico.cedula || '');
            $('#us_tipo').html(medico.tipo || 'Médico');
            $('#telefono_us').html(medico.telefono || '');
            $('#direccion_us').html(medico.direccion || '');
            $('#correo_us').html(medico.correo || '');
            $('#sexo_us').html(medico.sexo || '');
            $('#adicional_us').html(medico.adicional || '');
            
          
            if(medico.avatar) {
                console.log('Cargando avatar desde:', medico.avatar);
                $('#avatar1').attr('src', medico.avatar);
                $('#avatar2').attr('src', medico.avatar);
                $('#avatar3').attr('src', medico.avatar);
                $('#avatar4').attr('src', medico.avatar);
            } else {
                // Usar avatar por defecto
                $('#avatar1').attr('src', '../../img/avatarDES.jpg');
                $('#avatar2').attr('src', '../../img/avatarDES.jpg');
                $('#avatar3').attr('src', '../../img/avatarDES.jpg');
                $('#avatar4').attr('src', '../../img/avatarDES.jpg');
            }
            
            console.log('Datos actualizados correctamente');
        },
        error: function(xhr, status, error) {
            console.error('Error en la petición AJAX:', error);
            console.error('Respuesta del servidor:', xhr.responseText);
            $('#nombre_us').html('Error de conexión');
            
            // Cargar avatar por defecto en caso de error
            $('#avatar1').attr('src', '../../img/avatarDES.jpg');
            $('#avatar2').attr('src', '../../img/avatarDES.jpg');
            $('#avatar3').attr('src', '../../img/avatarDES.jpg');
            $('#avatar4').attr('src', '../../img/avatarDES.jpg');
        }
    });
}

    $(document).on('click', '.edit', function(e) {
        e.preventDefault();
        funcion = 'capturar_datos';
        edit = true;
        
        $.ajax({
            url: '../../controlador/MedicoController.php',
            type: 'POST',
            data: {funcion: funcion, id_medico: id_usuario},
            dataType: 'json',
            success: function(medico) {
                console.log('Datos a editar:', medico);
                
                if(medico.error) {
                    console.error('Error:', medico.error);
                    return;
                }
                
                $('#telefono').val(medico.telefono);
                $('#direccion').val(medico.direccion);
                $('#correo').val(medico.correo);
                $('#sexo').val(medico.sexo);
                $('#adicional').val(medico.adicional);
                
                $('#telefono, #direccion, #correo, #sexo, #adicional').prop('disabled', false);
                $('.btn-outline-success').removeClass('btn-outline-success').addClass('btn-success');
            },
            error: function(xhr, status, error) {
                console.error('Error al capturar datos:', error);
            }
        });
    });
    
    $('#form-usuario').submit(function(e) {
        e.preventDefault();
        
        if (edit == true) {
            let telefono = $('#telefono').val();
            let direccion = $('#direccion').val();
            let correo = $('#correo').val();
            let sexo = $('#sexo').val();
            let adicional = $('#adicional').val();
            funcion = 'editar_medico';
            
            $.ajax({
                url: '../../controlador/MedicoController.php',
                type: 'POST',
                data: {
                    id_medico: id_usuario,
                    funcion: funcion,
                    telefono: telefono,
                    direccion: direccion,
                    correo: correo,
                    sexo: sexo,
                    adicional: adicional
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta editar:', response);
                    
                    if (response.success) {
                        $('#editado').show(1000);
                        setTimeout(function() { $('#editado').hide(2000); }, 1000);
                        $('#form-usuario').trigger('reset');
                        edit = false;
                        buscar_medico(id_usuario);
                    } else {
                        $('#noeditado').show(1000);
                        setTimeout(function() { $('#noeditado').hide(2000); }, 1000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al editar:', error);
                    $('#noeditado').show(1000);
                    setTimeout(function() { $('#noeditado').hide(2000); }, 1000);
                }
            });
        } else {
            $('#noeditado').show(1000);
            setTimeout(function() { $('#noeditado').hide(2000); }, 1000);
            $('#form-usuario').trigger('reset');
        }
    });
    
    $('#form-pass').submit(function(e) {
        e.preventDefault();
        
        let oldpass = $('#oldpass').val();
        let newpass = $('#newpass').val();
        funcion = 'cambiar_contra';
        
        $.ajax({
            url: '../../controlador/LoginMedico.php',
            type: 'POST',
            data: {
                id_medico: id_usuario,
                funcion: funcion,
                oldpass: oldpass,
                newpass: newpass
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta cambio contraseña:', response);
                
                if (response.resultado == 'update') {
                    $('#update').show(1000);
                    setTimeout(function() { $('#update').hide(2000); }, 1000);
                    $('#form-pass').trigger('reset');
                } else {
                    $('#noupdate').show(1000);
                    setTimeout(function() { $('#noupdate').hide(2000); }, 1000);
                    $('#form-pass').trigger('reset');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cambiar contraseña:', error);
            }
        });
    });
    
    $('#form-photo').submit(function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#form-photo')[0]);
        formData.append('funcion', 'cambiar_foto');
        formData.append('id_medico', id_usuario);
        
        $.ajax({
            url: '../../controlador/MedicoController.php',
            type: 'POST',
            data: formData,
            cache: false,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta cambio foto:', response);
                
                if (response.alert == 'edit') {
                    $('#avatar1').attr('src', response.ruta);
                    $('#edit').show(1000);
                    setTimeout(function() { $('#edit').hide(2000); }, 1000);
                    $('#form-photo').trigger('reset');
                    buscar_medico(id_usuario);
                } else {
                    $('#noedit').show(1000);
                    setTimeout(function() { $('#noedit').hide(2000); }, 1000);
                    $('#form-photo').trigger('reset');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cambiar foto:', error);
            }
        });
    });
});
