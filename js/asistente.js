$(document).ready(function() {
    var id_usuario = $('#id_usuario').val();
    var funcion = '';
    var edit = false;

    console.log('ID Asistente desde PHP:', id_usuario);
    buscar_asistente(id_usuario);

    function buscar_asistente(dato) {
        funcion = 'buscar_asistente';
        console.log('Buscando asistente con dato:', dato);
        
        $.ajax({
            url: '../../controlador/AsistenteController.php',
            type: 'POST',
            data: {dato: dato, funcion: funcion},
            dataType: 'json',
            success: function(asistente) {
                console.log('Asistente recibido:', asistente);
                
                if(asistente.error) {
                    console.error('Error:', asistente.error);
                    $('#nombre_us').html('Error: ' + asistente.error);
                    return;
                }
                
                $('#nombre_us').html(asistente.nombre || '');
                $('#apellidos_us').html(asistente.apellidos || '');
                $('#edad').html(asistente.fecha_nacimiento || '');
                $('#cedula_us').html(asistente.cedula || '');
                $('#us_tipo').html(asistente.tipo || 'Asistente');
                $('#telefono_us').html(asistente.telefono || '');
                $('#direccion_us').html(asistente.direccion || '');
                $('#correo_us').html(asistente.correo || '');
                $('#sexo_us').html(asistente.sexo || '');
                $('#adicional_us').html(asistente.adicional || '');
                
                if(asistente.avatar) {
                    $('#avatar1').attr('src', asistente.avatar);
                    $('#avatar2').attr('src', asistente.avatar);
                    $('#avatar3').attr('src', asistente.avatar);
                    $('#avatar4').attr('src', asistente.avatar);
                }
                
                console.log('Datos actualizados correctamente');
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX:', error);
                console.error('Respuesta del servidor:', xhr.responseText);
                $('#nombre_us').html('Error de conexión');
            }
        });
    }

    // Evento para el botón editar
    $(document).on('click', '.edit', function(e) {
        e.preventDefault();
        funcion = 'capturar_datos';
        edit = true;
        
        $.ajax({
            url: '../../controlador/AsistenteController.php',
            type: 'POST',
            data: {funcion: funcion, id_asistente: id_usuario},
            dataType: 'json',
            success: function(asistente) {
                console.log('Datos a editar:', asistente);
                
                if(asistente.error) {
                    console.error('Error:', asistente.error);
                    return;
                }
                
                $('#telefono').val(asistente.telefono);
                $('#direccion').val(asistente.direccion);
                $('#correo').val(asistente.correo);
                $('#sexo').val(asistente.sexo);
                $('#adicional').val(asistente.adicional);
                
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
            funcion = 'editar_asistente';
            
            $.ajax({
                url: '../../controlador/AsistenteController.php',
                type: 'POST',
                data: {
                    id_asistente: id_usuario,
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
                        buscar_asistente(id_usuario);
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
            url: '../../controlador/LoginAsistente.php',
            type: 'POST',
            data: {
                id_asistente: id_usuario,
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
        formData.append('id_asistente', id_usuario);
        
        $.ajax({
            url: '../../controlador/AsistenteController.php',
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
                    buscar_asistente(id_usuario);
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
