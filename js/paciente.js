$(document).ready(function() {
    var id_usuario = $('#id_usuario').val();
    var funcion = '';
    var edit = false;
    
    console.log('ID Paciente desde PHP:', id_usuario);
    
    if(id_usuario && id_usuario !== '') {
        buscar_paciente(id_usuario);
    } else {
        console.error('ID de usuario no válido');
        $('#nombre_us').html('Error: ID de usuario no válido');
    }

    function buscar_paciente(dato) {
        funcion = 'buscar_paciente';
        console.log('Buscando paciente con dato:', dato);
        
        $.ajax({
            url: '../../controlador/PacienteController.php',
            type: 'POST',
            data: {dato: dato, funcion: funcion},
            dataType: 'json',
            timeout: 10000,
            success: function(paciente) {
                console.log('Paciente recibido:', paciente);
                
                if(paciente.error) {
                    console.error('Error:', paciente.error);
                    $('#nombre_us').html('Error: ' + paciente.error);
                    return;
                }
                
                // Datos básicos
                if(paciente.nombre) $('#nombre_us').html(paciente.nombre);
                if(paciente.apellidos) $('#apellidos_us').html(paciente.apellidos);
                if(paciente.fecha_nacimiento) $('#edad').html(paciente.fecha_nacimiento);
                if(paciente.cedula) $('#cedula_us').html(paciente.cedula);
                if(paciente.tipo) $('#us_tipo').html(paciente.tipo);
                if(paciente.telefono) $('#telefono_us').html(paciente.telefono);
                if(paciente.correo) $('#correo_us').html(paciente.correo);
                if(paciente.sexo) $('#sexo_us').html(paciente.sexo);
                if(paciente.adicional) $('#adicional_us').html(paciente.adicional);
                
                // Mostrar dirección completa en "Sobre mi"
                if(paciente.direccion) {
                    $('#direccion_us').html(paciente.direccion);
                } else {
                    $('#direccion_us').html('-');
                }
                
                // Cargar dirección en los campos de edición (si existe)
                if(paciente.direccion && paciente.direccion !== '-') {
                    cargarDireccionEnCampos(paciente.direccion);
                } else {
                    // Si no hay dirección, cargar selects vacíos
                    cargarEstados();
                }
                
                // Avatar
                if(paciente.avatar && paciente.avatar !== '../../img/') {
                    $('#avatar1').attr('src', paciente.avatar);
                    $('#avatar2').attr('src', paciente.avatar);
                    $('#avatar3').attr('src', paciente.avatar);
                    $('#avatar4').attr('src', paciente.avatar);
                }
                
                console.log('Datos actualizados correctamente');
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX:', error);
                $('#nombre_us').html('Error de conexión: ' + status);
                // Cargar estados por defecto
                cargarEstados();
            }
        });
    }
    
   // Función para parsear la dirección y cargarla en los campos
function cargarDireccionEnCampos(direccion_completa) {
    console.log('Parseando dirección:', direccion_completa);
    
    // Separar la dirección detallada del resto
    let partes = direccion_completa.split(' - ');
    let ubicacion = partes[0]; // "Estado, Ciudad, Municipio, Parroquia"
    let direccion_detallada = partes.length > 1 ? partes[1] : '';
    
    // Separar los componentes de ubicación
    let ubicacion_partes = ubicacion.split(', ');
    
    // Asignar dirección detallada
    $('#direccion_detallada').val(direccion_detallada);
    
    // Guardar la dirección original
    $('#direccion').val(direccion_completa);
    
    // Cargar estados y luego seleccionar el correcto
    cargarEstadosConSeleccion(ubicacion_partes);
}





function cargarEstadosConSeleccion(ubicacion_partes) {
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'listar_estados' },
        dataType: 'json',
        success: function(estados) {
            let options = '<option value="">Seleccione un estado...</option>';
            for (let estado of estados) {
                options += `<option value="${estado.id_estado}">${estado.estado}</option>`;
            }
            $('#estado').html(options);
            $('#estado').prop('disabled', false);
            
            // Si tenemos un estado en la dirección, seleccionarlo
            if (ubicacion_partes[0] && ubicacion_partes[0] !== '') {
                let estado_nombre = ubicacion_partes[0].trim();
                $('#estado option').each(function() {
                    if ($(this).text() === estado_nombre) {
                        $(this).prop('selected', true);
                        // Cargar ciudades después de seleccionar el estado
                        let id_estado = $(this).val();
                        if (id_estado) {
                            cargarCiudadesConSeleccion(id_estado, ubicacion_partes);
                        }
                    }
                });
            }
        },
        error: function() {
            cargarEstadosFallback();
        }
    });
}

function cargarCiudadesConSeleccion(id_estado, ubicacion_partes) {
    if (!id_estado) return;
    
    $('#ciudad').html('<option value="">Cargando ciudades...</option>').prop('disabled', false);
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'listar_ciudades', id_estado: id_estado },
        dataType: 'json',
        success: function(ciudades) {
            let options = '<option value="">Seleccione una ciudad...</option>';
            for (let ciudad of ciudades) {
                options += `<option value="${ciudad.id_ciudad}">${ciudad.ciudad}</option>`;
            }
            $('#ciudad').html(options).prop('disabled', false);
            
            // Seleccionar la ciudad si existe
            if (ubicacion_partes[1] && ubicacion_partes[1] !== '') {
                let ciudad_nombre = ubicacion_partes[1].trim();
                $('#ciudad option').each(function() {
                    if ($(this).text() === ciudad_nombre) {
                        $(this).prop('selected', true);
                        // Cargar municipios
                        cargarMunicipiosConSeleccion(id_estado, ubicacion_partes);
                    }
                });
            } else {
                cargarMunicipios(id_estado);
            }
        },
        error: function() {
            $('#ciudad').html('<option value="">Error al cargar ciudades</option>').prop('disabled', false);
        }
    });
}

function cargarMunicipiosConSeleccion(id_estado, ubicacion_partes) {
    if (!id_estado) return;
    
    $('#municipio').html('<option value="">Cargando municipios...</option>').prop('disabled', false);
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'listar_municipios', id_estado: id_estado },
        dataType: 'json',
        success: function(municipios) {
            let options = '<option value="">Seleccione un municipio...</option>';
            for (let municipio of municipios) {
                options += `<option value="${municipio.id_municipio}">${municipio.municipio}</option>`;
            }
            $('#municipio').html(options).prop('disabled', false);
            
            // Seleccionar el municipio si existe
            if (ubicacion_partes[2] && ubicacion_partes[2] !== '') {
                let municipio_nombre = ubicacion_partes[2].trim();
                $('#municipio option').each(function() {
                    if ($(this).text() === municipio_nombre) {
                        $(this).prop('selected', true);
                        // Cargar parroquias
                        let id_municipio = $(this).val();
                        if (id_municipio) {
                            cargarParroquiasConSeleccion(id_municipio, ubicacion_partes);
                        }
                    }
                });
            }
        },
        error: function() {
            $('#municipio').html('<option value="">Error al cargar municipios</option>').prop('disabled', false);
        }
    });
}

function cargarParroquiasConSeleccion(id_municipio, ubicacion_partes) {
    if (!id_municipio) return;
    
    $('#parroquia').html('<option value="">Cargando parroquias...</option>').prop('disabled', false);
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'listar_parroquias', id_municipio: id_municipio },
        dataType: 'json',
        success: function(parroquias) {
            let options = '<option value="">Seleccione una parroquia...</option>';
            for (let parroquia of parroquias) {
                options += `<option value="${parroquia.id_parroquia}">${parroquia.parroquia}</option>`;
            }
            $('#parroquia').html(options).prop('disabled', false);
            
            // Seleccionar la parroquia si existe
            if (ubicacion_partes[3] && ubicacion_partes[3] !== '') {
                let parroquia_nombre = ubicacion_partes[3].trim();
                $('#parroquia option').each(function() {
                    if ($(this).text() === parroquia_nombre) {
                        $(this).prop('selected', true);
                    }
                });
            }
        },
        error: function() {
            $('#parroquia').html('<option value="">Error al cargar parroquias</option>').prop('disabled', false);
        }
    });
}





    // Evento para el botón editar
    $(document).on('click', '.edit', function(e) {
        e.preventDefault();
        funcion = 'capturar_datos';
        edit = true;
        
        $.ajax({
            url: '../../controlador/PacienteController.php',
            type: 'POST',
            data: {funcion: funcion, id_paciente: id_usuario},
            dataType: 'json',
            success: function(paciente) {
                console.log('Datos a editar:', paciente);
                
                if(paciente.error) {
                    console.error('Error:', paciente.error);
                    return;
                }
                
                $('#telefono').val(paciente.telefono);
                $('#correo').val(paciente.correo);
                $('#sexo').val(paciente.sexo);
                $('#adicional').val(paciente.adicional);
                
                // Habilitar todos los campos de edición
                $('#telefono, #correo, #sexo, #adicional, #estado, #ciudad, #municipio, #parroquia, #direccion_detallada').prop('disabled', false);
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
            // Construir dirección completa
            var estado_nombre = $('#estado option:selected').text();
            var ciudad_nombre = $('#ciudad option:selected').text();
            var municipio_nombre = $('#municipio option:selected').text();
            var parroquia_nombre = $('#parroquia option:selected').text();
            var direccion_detallada = $('#direccion_detallada').val();
            
            var direccion_completa = '';
            
            // Solo agregar si no son los valores por defecto
            if (estado_nombre && estado_nombre !== 'Seleccione un estado...' && estado_nombre !== '') {
                direccion_completa += estado_nombre;
            }
            if (ciudad_nombre && ciudad_nombre !== 'Seleccione una ciudad...' && ciudad_nombre !== '' && ciudad_nombre !== 'Cargando ciudades...') {
                direccion_completa += (direccion_completa ? ', ' : '') + ciudad_nombre;
            }
            if (municipio_nombre && municipio_nombre !== 'Seleccione un municipio...' && municipio_nombre !== '' && municipio_nombre !== 'Cargando municipios...') {
                direccion_completa += (direccion_completa ? ', ' : '') + municipio_nombre;
            }
            if (parroquia_nombre && parroquia_nombre !== 'Seleccione una parroquia...' && parroquia_nombre !== '' && parroquia_nombre !== 'Cargando parroquias...') {
                direccion_completa += (direccion_completa ? ', ' : '') + parroquia_nombre;
            }
            if (direccion_detallada && direccion_detallada !== '') {
                direccion_completa += (direccion_completa ? ' - ' : '') + direccion_detallada;
            }
            
            let telefono = $('#telefono').val();
            let direccion = direccion_completa;
            let correo = $('#correo').val();
            let sexo = $('#sexo').val();
            let adicional = $('#adicional').val();
            funcion = 'editar_paciente';
            
            console.log('Guardando dirección:', direccion);
            
            $.ajax({
                url: '../../controlador/PacienteController.php',
                type: 'POST',
                data: {
                    id_paciente: id_usuario,
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
                        // Deshabilitar campos después de guardar
                        $('#estado, #ciudad, #municipio, #parroquia, #direccion_detallada').prop('disabled', true);
                        buscar_paciente(id_usuario);
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
    
    // Función para parsear la dirección y cargarla en los campos
function cargarDireccionEnCampos(direccion_completa) {
    console.log('Parseando dirección:', direccion_completa);
    
    // Separar la dirección detallada del resto
    let partes = direccion_completa.split(' - ');
    let ubicacion = partes[0]; // "Estado, Ciudad, Municipio, Parroquia"
    let direccion_detallada = partes.length > 1 ? partes[1] : '';
    
    // Separar los componentes de ubicación
    let ubicacion_partes = ubicacion.split(', ');
    
    // Asignar dirección detallada
    $('#direccion_detallada').val(direccion_detallada);
    
    // Guardar la dirección original
    $('#direccion').val(direccion_completa);
    
    // Cargar estados y luego seleccionar el correcto
    cargarEstadosConSeleccion(ubicacion_partes);
}


 //==================== FUNCIONES DE UBICACIÓN UNIFICADAS ====================

function cargarEstados() {
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'listar_estados' },
        dataType: 'json',
        success: function(estados) {
            let options = '<option value="">Seleccione un estado...</option>';
            for (let estado of estados) {
                options += `<option value="${estado.id_estado}">${estado.estado}</option>`;
            }
            $('#estado').html(options);
            $('#estado').prop('disabled', false);
        },
        error: function() {
            cargarEstadosFallback();
        }
    });
}

function cargarEstadosFallback() {
    const estados = [
        {id_estado: 1, estado: 'Amazonas'}, {id_estado: 2, estado: 'Anzoátegui'},
        {id_estado: 3, estado: 'Apure'}, {id_estado: 4, estado: 'Aragua'},
        {id_estado: 5, estado: 'Barinas'}, {id_estado: 6, estado: 'Bolívar'},
        {id_estado: 7, estado: 'Carabobo'}, {id_estado: 8, estado: 'Cojedes'},
        {id_estado: 9, estado: 'Delta Amacuro'}, {id_estado: 10, estado: 'Falcón'},
        {id_estado: 11, estado: 'Guárico'}, {id_estado: 12, estado: 'Lara'},
        {id_estado: 13, estado: 'Mérida'}, {id_estado: 14, estado: 'Miranda'},
        {id_estado: 15, estado: 'Monagas'}, {id_estado: 16, estado: 'Nueva Esparta'},
        {id_estado: 17, estado: 'Portuguesa'}, {id_estado: 18, estado: 'Sucre'},
        {id_estado: 19, estado: 'Táchira'}, {id_estado: 20, estado: 'Trujillo'},
        {id_estado: 21, estado: 'La Guaira'}, {id_estado: 22, estado: 'Yaracuy'},
        {id_estado: 23, estado: 'Zulia'}, {id_estado: 24, estado: 'Distrito Capital'}
    ];
    let options = '<option value="">Seleccione un estado...</option>';
    for (let estado of estados) {
        options += `<option value="${estado.id_estado}">${estado.estado}</option>`;
    }
    $('#estado').html(options);
    $('#estado').prop('disabled', false);
}

function cargarCiudades(id_estado) {
    if (!id_estado) {
        $('#ciudad').html('<option value="">Seleccione un estado primero...</option>').prop('disabled', true);
        return;
    }
    
    $('#ciudad').html('<option value="">Cargando ciudades...</option>').prop('disabled', true);
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'listar_ciudades', id_estado: id_estado },
        dataType: 'json',
        success: function(ciudades) {
            let options = '<option value="">Seleccione una ciudad...</option>';
            for (let ciudad of ciudades) {
                options += `<option value="${ciudad.id_ciudad}">${ciudad.ciudad}</option>`;
            }
            $('#ciudad').html(options).prop('disabled', false);
        },
        error: function() {
            $('#ciudad').html('<option value="">Error al cargar ciudades</option>').prop('disabled', false);
        }
    });
}

// IMPORTANTE: Municipios se cargan POR ESTADO (NO por ciudad)
function cargarMunicipios(id_estado) {
    if (!id_estado) {
        $('#municipio').html('<option value="">Seleccione un estado primero...</option>').prop('disabled', true);
        $('#parroquia').html('<option value="">Seleccione un municipio primero...</option>').prop('disabled', true);
        return;
    }
    
    $('#municipio').html('<option value="">Cargando municipios...</option>').prop('disabled', true);
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'listar_municipios', id_estado: id_estado },
        dataType: 'json',
        success: function(municipios) {
            let options = '<option value="">Seleccione un municipio...</option>';
            for (let municipio of municipios) {
                options += `<option value="${municipio.id_municipio}">${municipio.municipio}</option>`;
            }
            $('#municipio').html(options).prop('disabled', false);
            // Resetear parroquias cuando cambia el municipio
            $('#parroquia').html('<option value="">Seleccione un municipio primero...</option>').prop('disabled', true);
        },
        error: function() {
            $('#municipio').html('<option value="">Error al cargar municipios</option>').prop('disabled', false);
        }
    });
}

function cargarParroquias(id_municipio) {
    if (!id_municipio) {
        $('#parroquia').html('<option value="">Seleccione un municipio primero...</option>').prop('disabled', true);
        return;
    }
    
    $('#parroquia').html('<option value="">Cargando parroquias...</option>').prop('disabled', true);
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'listar_parroquias', id_municipio: id_municipio },
        dataType: 'json',
        success: function(parroquias) {
            let options = '<option value="">Seleccione una parroquia...</option>';
            for (let parroquia of parroquias) {
                options += `<option value="${parroquia.id_parroquia}">${parroquia.parroquia}</option>`;
            }
            $('#parroquia').html(options).prop('disabled', false);
        },
        error: function() {
            $('#parroquia').html('<option value="">Error al cargar parroquias</option>').prop('disabled', false);
        }
    });
}
    $('#form-pass').submit(function(e) {
        e.preventDefault();
        
        let oldpass = $('#oldpass').val();
        let newpass = $('#newpass').val();
        funcion = 'cambiar_contra';
        
        $.ajax({
            url: '../../controlador/LoginPaciente.php',
            type: 'POST',
            data: {
                id_paciente: id_usuario,
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
    // Eventos de cambio para carga de ubicación
$(document).on('change', '#estado', function() {
    let id_estado = $(this).val();
    if (id_estado) {
        cargarCiudades(id_estado);   // Carga ciudades
        cargarMunicipios(id_estado); // Carga municipios (CORRECTO: por estado)
    } else {
        $('#ciudad').html('<option value="">Seleccione un estado primero...</option>').prop('disabled', true);
        $('#municipio').html('<option value="">Seleccione un estado primero...</option>').prop('disabled', true);
        $('#parroquia').html('<option value="">Seleccione un municipio primero...</option>').prop('disabled', true);
    }
});

$(document).on('change', '#municipio', function() {
    let id_municipio = $(this).val();
    if (id_municipio) {
        cargarParroquias(id_municipio);
    } else {
        $('#parroquia').html('<option value="">Seleccione un municipio primero...</option>').prop('disabled', true);
    }
});
    
    $('#form-photo').submit(function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#form-photo')[0]);
        formData.append('funcion', 'cambiar_foto');
        formData.append('id_paciente', id_usuario);
        
        $.ajax({
            url: '../../controlador/PacienteController.php',
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
                    buscar_paciente(id_usuario);
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


