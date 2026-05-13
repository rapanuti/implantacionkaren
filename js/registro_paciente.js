//***   crear_paciente */
$(document).ready(function() {
    $('#form-registro').submit(function(e) {
        e.preventDefault();
        
        var pass = $('#pass').val();
        var confirm_pass = $('#confirm_pass').val();
        
        if(pass !== confirm_pass) {
            mostrarError('Las contraseñas no coinciden');
            return false;
        }
        
        if(pass.length < 6) {
            mostrarError('La contraseña debe tener al menos 6 caracteres');
            return false;
        }
        
        // Obtener valores de ubicación (guardamos los nombres, no los IDs)
        var estado_nombre = $('#estado option:selected').text();
        var ciudad_nombre = $('#ciudad option:selected').text();
        var municipio_nombre = $('#municipio option:selected').text();
        var parroquia_nombre = $('#parroquia option:selected').text();
        
        // Construir dirección completa con todos los campos
        var direccion_completa = '';
        if (estado_nombre && estado_nombre !== 'Seleccione un estado...') {
            direccion_completa += estado_nombre;
        }
        if (ciudad_nombre && ciudad_nombre !== 'Seleccione una ciudad...') {
            direccion_completa += (direccion_completa ? ', ' : '') + ciudad_nombre;
        }
        if (municipio_nombre && municipio_nombre !== 'Seleccione un municipio...') {
            direccion_completa += (direccion_completa ? ', ' : '') + municipio_nombre;
        }
        if (parroquia_nombre && parroquia_nombre !== 'Seleccione una parroquia...') {
            direccion_completa += (direccion_completa ? ', ' : '') + parroquia_nombre;
        }
        
        var direccion_detallada = $('#direccion').val();
        if (direccion_detallada) {
            direccion_completa += (direccion_completa ? ' - ' : '') + direccion_detallada;
        }
        
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : ($('#csrf_token_reg').val() || '');
        
        var datos = {
            csrf_token: csrfToken,
            funcion: 'crear_paciente',
            nombre: $('#nombre').val(),
            apellidos: $('#apellidos').val(),
            fecha_nacimiento: $('#fecha_nacimiento').val(),
            cedula: $('#cedula').val(),
            telefono: $('#telefono').val(),
            direccion: direccion_completa,  // Guardamos la dirección completa
            correo: $('#correo').val(),
            sexo: $('#sexo').val(),
            adicional: $('#adicional').val(),
            pass: pass
        };
        
        console.log('Enviando datos:', datos);
        
        var $submitBtn = $(this).find('button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando cuenta...');
        
        $.ajax({
            url: '../controlador/RegistroPacienteController.php',
            type: 'POST',
            data: datos,
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta:', response);
                if(response.success) {
                    mostrarExito(response.message);
                    setTimeout(function() {
                        window.location.href = '../vista/login_paciente.php';
                    }, 2000);
                } else {
                    mostrarError(response.message);
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                console.error('Respuesta:', xhr.responseText);
                mostrarError('Error de conexión: ' + xhr.status);
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    function mostrarError(mensaje) {
        $('#error-message').text(mensaje);
        $('#alert-error').fadeIn();
        setTimeout(function() { $('#alert-error').fadeOut(); }, 5000);
    }
    
    function mostrarExito(mensaje) {
        $('#success-message').text(mensaje);
        $('#alert-success').fadeIn();
    }
});