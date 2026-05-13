$(document).ready(function() {
    // ==================== LISTADO DE CONSULTORIOS ====================
    if ($('#contenedor_consultorios').length) {
        cargarEstadisticas();
        cargarConsultorios();
        
        $('#btnBuscar').click(function() {
            cargarConsultorios($('#buscar_consultorio').val());
        });
        
        $('#buscar_consultorio').keypress(function(e) {
            if (e.which == 13) {
                cargarConsultorios($(this).val());
            }
        });
        
        $('#btnNuevoConsultorio').click(function() {
            window.location.href = 'adm_consultorio_crear.php';
        });
        
        $(document).on('click', '.btn-eliminar', function() {
            $('#eliminar_id').val($(this).data('id'));
            $('#modalEliminar').modal('show');
        });
        
        $('#confirmarEliminar').click(function() {
            eliminarConsultorio($('#eliminar_id').val());
        });
    }
    
    // ==================== DETALLE DE CONSULTORIO ====================
    if ($('#id_consultorio').length && $('#detalle_nombre').length) {
        cargarDetalleConsultorio();
        
        $('#btnAsignarMedico').click(function() {
            asignarMedico();
        });
        
        $(document).on('click', '.btn-remover-medico', function() {
            if (confirm('¿Está seguro de remover este médico del consultorio?')) {
                removerMedico($(this).data('id'));
            }
        });
    }
    
    // ==================== CREAR CONSULTORIO ====================
    if ($('#formCrearConsultorio').length) {
        cargarEstados();
        cargarListaEspecialidades();
        
        // Vista previa en tiempo real
        $('#nombre, #ciudad, #descripcion, #telefono, #email').on('input', function() {
            actualizarPreview();
        });
        
        $('#formCrearConsultorio').submit(function(e) {
            e.preventDefault();
            crearConsultorio();
        });
    }
    
    // ==================== EDITAR CONSULTORIO ====================
    if ($('#formEditarConsultorio').length) {
        cargarDatosConsultorio();
        cargarEstados();
        cargarListaEspecialidades();
        
        $('#volver_detalle').click(function(e) {
            e.preventDefault();
            let id = $('#id_consultorio').val();
            window.location.href = 'adm_consultorio_detalle.php?id=' + id;
        });
        
        // Vista previa en tiempo real
        $('#nombre, #ciudad, #descripcion, #telefono, #email').on('input', function() {
            actualizarPreview();
        });
        
        $('#formEditarConsultorio').submit(function(e) {
            e.preventDefault();
            editarConsultorio();
        });
    }
    
    // ==================== HORARIOS ====================
    if ($('#contenedor_horarios').length) {
        cargarNombreConsultorio();
        cargarHorarios();
        
        $('#volver_detalle').click(function(e) {
            e.preventDefault();
            let id = $('#id_consultorio').val();
            window.location.href = 'adm_consultorio_detalle.php?id=' + id;
        });
        
        $('#btnRefresh').click(function() {
            cargarHorarios();
        });
        
        $(document).on('click', '.btn-editar-horario', function() {
            let dia = $(this).data('dia');
            let turno = $(this).data('turno');
            let horaInicio = $(this).data('hora-inicio');
            let horaFin = $(this).data('hora-fin');
            let medicoId = $(this).data('medico-id') || '';
            let medicoNombre = $(this).data('medico-nombre') || '';
            
            $('#horario_dia').val(dia);
            $('#horario_turno').val(turno);
            $('#horario_dia_text').val(dia);
            $('#horario_turno_text').val(turno);
            $('#hora_inicio').val(horaInicio);
            $('#hora_fin').val(horaFin);
            
            if (medicoId) {
                $('#medico_asignado').val(medicoId);
            } else {
                $('#medico_asignado').val('');
            }
            
            $('#modalHorario').modal('show');
        });
        
        $('#btnGuardarHorario').click(function() {
            guardarHorario();
        });
    }
});

// ==================== FUNCIONES DE CONSULTORIOS ====================

function cargarEstadisticas() {
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'obtener_estadisticas' },
        dataType: 'json',
        success: function(data) {
            $('#total_consultorios').text(data.total_consultorios || 0);
            $('#total_activos').text(data.activos || 0);
        }
    });
}

function cargarConsultorios(busqueda = '') {
    $('#contenedor_consultorios').html('<div class="col-12 text-center"><div class="spinner-border text-primary"></div><p>Cargando consultorios...</p></div>');
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'listar_consultorios', busqueda: busqueda },
        dataType: 'json',
        success: function(consultorios) {
            let html = '';
            
            if (consultorios.length === 0) {
                html = '<div class="col-12 text-center"><div class="alert alert-info">No se encontraron consultorios</div></div>';
            } else {
                for (let c of consultorios) {
                    html += `
                        <div class="col-md-4 col-sm-6">
                            <div class="card consultorio-card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0"><i class="fas fa-building"></i> ${escapeHtml(c.nombre)}</h5>
                                </div>
                                <div class="card-body">
                                    <p><i class="fas fa-map-marker-alt text-danger"></i> <strong>${escapeHtml(c.ciudad || 'No especificada')}</strong></p>
                                    <p class="text-muted small">${escapeHtml(c.direccion || '')}</p>
                                    <p><i class="fas fa-phone"></i> ${c.telefono || 'No disponible'}</p>
                                    <p><i class="fas fa-user-md"></i> <span class="badge-medicos">${c.total_medicos || 0} Médicos asignados</span></p>
                                    <p><i class="fas fa-clock"></i> ${c.apertura || '08:00'} - ${c.cierre || '17:00'}</p>
                                </div>
                                <div class="card-footer text-right">
                                    <a href="adm_consultorio_detalle.php?id=${c.id_consultorio}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="${c.id_consultorio}">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }
            }
            $('#contenedor_consultorios').html(html);
        },
        error: function() {
            $('#contenedor_consultorios').html('<div class="col-12 text-center"><div class="alert alert-danger">Error al cargar consultorios</div></div>');
        }
    });
}

function eliminarConsultorio(id) {
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'eliminar_consultorio', id_consultorio: id },
        dataType: 'json',
        success: function(response) {
            if (response.resultado === 'eliminado') {
                $('#modalEliminar').modal('hide');
                cargarConsultorios();
                cargarEstadisticas();
                mostrarAlerta('Consultorio eliminado correctamente', 'success');
            } else {
                mostrarAlerta('Error al eliminar el consultorio', 'error');
            }
        }
    });
}

function cargarDetalleConsultorio() {
    let id = $('#id_consultorio').val();
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'obtener_detalle', id_consultorio: id },
        dataType: 'json',
        success: function(data) {
            $('#consultorio_nombre').text(data.nombre);
            $('#detalle_nombre').text(data.nombre);
            $('#detalle_ciudad').text(data.ciudad);
            $('#detalle_horario').text(data.apertura + ' - ' + data.cierre);
            $('#detalle_telefono').text(data.telefono || '-');
            $('#detalle_email').text(data.email || '-');
            $('#detalle_direccion').text(data.direccion || '-');
            $('#detalle_descripcion').html(data.descripcion || '<p class="text-muted">Sin descripción</p>');
            $('#total_citas').text(Math.floor(Math.random() * 50) + 10);
            
            // Especialidades
            let espHtml = '';
            if (data.especialidades && data.especialidades.length > 0) {
                for (let esp of data.especialidades) {
                    espHtml += `<span class="especialidad-badge">${escapeHtml(esp)}</span>`;
                }
            } else {
                espHtml = '<p class="text-muted text-center">No hay especialidades registradas</p>';
            }
            $('#contenedor_especialidades').html(espHtml);
            
            // Médicos
            let medHtml = '';
            if (data.medicos && data.medicos.length > 0) {
                for (let med of data.medicos) {
                    medHtml += `
                        <div class="medico-item p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><i class="fas fa-user-md text-info"></i> ${escapeHtml(med.nombre)}</strong><br>
                                    <small>Cédula: ${med.cedula} | Tel: ${med.telefono || '-'}</small>
                                </div>
                                <button class="btn btn-danger btn-sm btn-remover-medico" data-id="${med.id}">
                                    <i class="fas fa-user-minus"></i> Remover
                                </button>
                            </div>
                        </div>
                    `;
                }
            } else {
                medHtml = '<p class="text-muted text-center">No hay médicos asignados</p>';
            }
            $('#contenedor_medicos').html(medHtml);
            
            // Cargar lista de médicos para el modal
            cargarListaMedicos();
        }
    });
}

function cargarListaMedicos() {
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'listar_medicos_disponibles' },
        dataType: 'json',
        success: function(medicos) {
            let options = '<option value="">Seleccione un médico...</option>';
            for (let med of medicos) {
                options += `<option value="${med.id_medico}">${escapeHtml(med.nombre_medico)} ${escapeHtml(med.apellido_medico)} (${med.cedula_medico})</option>`;
            }
            $('#medico_seleccionado').html(options);
        }
    });
}

function asignarMedico() {
    let id_consultorio = $('#id_consultorio').val();
    let id_medico = $('#medico_seleccionado').val();
    
    if (!id_medico) {
        mostrarMensaje('Seleccione un médico', 'error');
        return;
    }
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'asignar_medico', id_consultorio: id_consultorio, id_medico: id_medico },
        dataType: 'json',
        success: function(response) {
            if (response.resultado === 'asignado') {
                mostrarMensaje('Médico asignado correctamente', 'success');
                setTimeout(function() {
                    $('#modalAsignarMedico').modal('hide');
                    cargarDetalleConsultorio();
                }, 1500);
            } else if (response.resultado === 'ya_asignado') {
                mostrarMensaje('El médico ya está asignado a este consultorio', 'error');
            } else {
                mostrarMensaje('Error al asignar el médico', 'error');
            }
        }
    });
}

function removerMedico(id_asignacion) {
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'remover_medico', id_asignacion: id_asignacion },
        dataType: 'json',
        success: function(response) {
            if (response.resultado === 'removido') {
                mostrarAlerta('Médico removido del consultorio', 'success');
                cargarDetalleConsultorio();
            } else {
                mostrarAlerta('Error al remover el médico', 'error');
            }
        }
    });
}

// ==================== FUNCIONES DE CREAR/EDITAR ====================

// ==================== SISTEMA DE UBICACIÓN ====================

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
        },
        error: function(xhr) {
            console.error('Error cargando estados:', xhr.responseText);
        }
    });
}

function cargarCiudades(id_estado) {
    if (!id_estado) {
        $('#ciudad').html('<option value="">Seleccione un estado primero...</option>').prop('disabled', true);
        $('#municipio').html('<option value="">Seleccione una ciudad primero...</option>').prop('disabled', true);
        $('#parroquia').html('<option value="">Seleccione un municipio primero...</option>').prop('disabled', true);
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
            $('#municipio').html('<option value="">Seleccione una ciudad primero...</option>').prop('disabled', true);
            $('#parroquia').html('<option value="">Seleccione un municipio primero...</option>').prop('disabled', true);
        },
        error: function(xhr) {
            console.error('Error cargando ciudades:', xhr.responseText);
            $('#ciudad').html('<option value="">Error al cargar ciudades</option>').prop('disabled', false);
        }
    });
}

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
            $('#parroquia').html('<option value="">Seleccione un municipio primero...</option>').prop('disabled', true);
        },
        error: function(xhr) {
            console.error('Error cargando municipios:', xhr.responseText);
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
        error: function(xhr) {
            console.error('Error cargando parroquias:', xhr.responseText);
            $('#parroquia').html('<option value="">Error al cargar parroquias</option>').prop('disabled', false);
        }
    });
}

// Eventos
$(document).on('change', '#estado', function() {
    let id_estado = $(this).val();
    if (id_estado) {
        cargarCiudades(id_estado);
        cargarMunicipios(id_estado);
    } else {
        $('#ciudad').html('<option value="">Seleccione un estado primero...</option>').prop('disabled', true);
        $('#municipio').html('<option value="">Seleccione un estado primero...</option>').prop('disabled', true);
        $('#parroquia').html('<option value="">Seleccione un municipio primero...</option>').prop('disabled', true);
    }
});

$(document).on('change', '#ciudad', function() {
    let ciudad_nombre = $('#ciudad option:selected').text();
    if ($('#preview_ciudad').length) {
        $('#preview_ciudad').text(ciudad_nombre || 'Ciudad');
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

// Función para cargar datos de ubicación al editar (para mostrar los nombres en lugar de IDs)
function cargarUbicacionParaEdicion(estado_id, ciudad_id, municipio_id, parroquia_id) {
    // Primero cargar estados y luego seleccionar
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'listar_estados' },
        dataType: 'json',
        success: function(estados) {
            $('#estado').html('<option value="">Seleccione un estado...</option>');
            for (let estado of estados) {
                let selected = (estado.id_estado == estado_id) ? 'selected' : '';
                $('#estado').append(`<option value="${estado.id_estado}" ${selected}>${estado.estado}</option>`);
            }
            
            if (estado_id) {
                cargarCiudades(estado_id, ciudad_id);
            }
        }
    });
}

// Modificar cargarCiudades para aceptar selección opcional
function cargarCiudades(id_estado, ciudad_seleccionada = null) {
    if (!id_estado) {
        $('#ciudad').html('<option value="">Primero seleccione un estado...</option>').prop('disabled', true);
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
                let selected = (ciudad.id_ciudad == ciudad_seleccionada) ? 'selected' : '';
                options += `<option value="${ciudad.id_ciudad}" data-nombre="${ciudad.ciudad}" ${selected}>${ciudad.ciudad}</option>`;
            }
            $('#ciudad').html(options).prop('disabled', false);
            
            if (ciudad_seleccionada) {
                $('#ciudad').trigger('change');
            }
        }
    });
}
function cargarListaEspecialidades() {
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'lista_especialidades' },
        dataType: 'json',
        success: function(especialidades) {
            let html = '';
            for (let esp of especialidades) {
                html += `
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input especialidad-check" value="${esp}" id="esp_${esp.replace(/\s/g, '_')}">
                        <label class="form-check-label" for="esp_${esp.replace(/\s/g, '_')}">${esp}</label>
                    </div>
                `;
            }
            $('#especialidades_container').html(html);
        }
    });
}

function obtenerEspecialidadesSeleccionadas() {
    let especialidades = [];
    $('.especialidad-check:checked').each(function() {
        especialidades.push($(this).val());
    });
    return especialidades;
}

function actualizarPreview() {
    $('#preview_nombre').text($('#nombre').val() || 'Nombre del Consultorio');
    $('#preview_ciudad').text($('#ciudad').val() || 'Ciudad');
    $('#preview_descripcion').text($('#descripcion').val() || 'Descripción');
    $('#preview_telefono').text($('#telefono').val() || '-');
    $('#preview_email').text($('#email').val() || '-');
}

function crearConsultorio() {
    let especialidades = obtenerEspecialidadesSeleccionadas();
    
    let datos = {
        funcion: 'crear_consultorio',
        nombre: $('#nombre').val(),
        descripcion: $('#descripcion').val(),
        apertura: $('#apertura').val(),
        cierre: $('#cierre').val(),
        telefono: $('#telefono').val(),
        email: $('#email').val(),
        id_estado: $('#estado').val(),
        id_ciudad: $('#ciudad').val(),
        id_municipio: $('#municipio').val(),
        id_parroquia: $('#parroquia').val(),
        direccion: $('#direccion').val(),
        especialidades: especialidades
    };
    
    // Validar campos requeridos
    if (!datos.nombre || !datos.id_estado || !datos.id_ciudad || !datos.direccion) {
        $('#errorMensaje').text('Complete los campos requeridos (*)');
        $('#alertError').show();
        setTimeout(function() { $('#alertError').hide(); }, 3000);
        return;
    }
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: datos,
        dataType: 'json',
        success: function(response) {
            if (response.resultado === 'creado') {
                $('#alertExito').show();
                setTimeout(function() {
                    window.location.href = 'adm_consultorios.php';
                }, 2000);
            } else {
                $('#errorMensaje').text('Error al crear el consultorio: ' + response.resultado);
                $('#alertError').show();
                setTimeout(function() { $('#alertError').hide(); }, 3000);
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            $('#errorMensaje').text('Error de conexión: ' + xhr.status);
            $('#alertError').show();
            setTimeout(function() { $('#alertError').hide(); }, 3000);
        }
    });
}

function cargarDatosConsultorio() {
    let id = $('#id_consultorio').val();
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'obtener_detalle', id_consultorio: id },
        dataType: 'json',
        success: function(data) {
            $('#nombre').val(data.nombre);
            $('#descripcion').val(data.descripcion);
            $('#apertura').val(data.apertura);
            $('#cierre').val(data.cierre);
            $('#telefono').val(data.telefono);
            $('#email').val(data.email);
            $('#direccion').val(data.direccion);
            
            // Guardar nombres actuales para el preview
            let estado_actual = data.estado;
            let ciudad_actual = data.ciudad;
            let municipio_actual = data.municipio;
            let parroquia_actual = data.parroquia;
            
            actualizarPreview();
            
            // Cargar estados y luego seleccionar el correspondiente
            $.ajax({
                url: '../../controlador/ConsultorioController.php',
                type: 'POST',
                data: { funcion: 'listar_estados' },
                dataType: 'json',
                success: function(estados) {
                    $('#estado').html('<option value="">Seleccione un estado...</option>');
                    for (let estado of estados) {
                        let selected = (estado.estado === estado_actual) ? 'selected' : '';
                        $('#estado').append(`<option value="${estado.id_estado}" ${selected}>${estado.estado}</option>`);
                    }
                    
                    if (estado_actual) {
                        // Después de cargar estados, cargar ciudades
                        let estado_id = $('#estado').val();
                        if (estado_id) {
                            cargarCiudades(estado_id);
                            // Esperar a que se carguen las ciudades para seleccionar la correcta
                            setTimeout(function() {
                                $('#ciudad option').each(function() {
                                    if ($(this).text() === ciudad_actual) {
                                        $(this).prop('selected', true);
                                        $('#ciudad').trigger('change');
                                    }
                                });
                            }, 500);
                        }
                    }
                }
            });
            
            // Marcar especialidades seleccionadas
            if (data.especialidades && data.especialidades.length > 0) {
                setTimeout(function() {
                    for (let esp of data.especialidades) {
                        let idCheck = `#esp_${esp.replace(/\s/g, '_')}`;
                        $(idCheck).prop('checked', true);
                    }
                }, 1000);
            }
        }
    });
}

function editarConsultorio() {
    let especialidades = obtenerEspecialidadesSeleccionadas();
    let id = $('#id_consultorio').val();
    
    let datos = {
        funcion: 'editar_consultorio',
        id_consultorio: id,
        nombre: $('#nombre').val(),
        descripcion: $('#descripcion').val(),
        apertura: $('#apertura').val(),
        cierre: $('#cierre').val(),
        telefono: $('#telefono').val(),
        email: $('#email').val(),
        estado: $('#estado').val(),
        ciudad: $('#ciudad').val(),
        municipio: $('#municipio').val(),
        parroquia: $('#parroquia').val(),
        direccion: $('#direccion').val(),
        especialidades: especialidades
    };
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: datos,
        dataType: 'json',
        success: function(response) {
            if (response.resultado === 'editado') {
                $('#alertExito').show();
                setTimeout(function() {
                    window.location.href = 'adm_consultorio_detalle.php?id=' + id;
                }, 2000);
            } else {
                $('#errorMensaje').text('Error al actualizar el consultorio');
                $('#alertError').show();
                setTimeout(function() { $('#alertError').hide(); }, 3000);
            }
        }
    });
}

// ==================== FUNCIONES DE HORARIOS ====================

function cargarNombreConsultorio() {
    let id = $('#id_consultorio').val();
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'obtener_detalle', id_consultorio: id },
        dataType: 'json',
        success: function(data) {
            $('#consultorio_nombre').text(data.nombre);
        }
    });
}

function cargarHorarios() {
    let id = $('#id_consultorio').val();
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: { funcion: 'obtener_horarios', id_consultorio: id },
        dataType: 'json',
        success: function(response) {
            let horarios = response.horarios;
            let medicos = response.medicos;
            
            // Llenar select de médicos en el modal
            let options = '<option value="">Sin asignar</option>';
            for (let med of medicos) {
                options += `<option value="${med.id}">${escapeHtml(med.nombre)}</option>`;
            }
            $('#medico_asignado').html(options);
            
            let dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            let html = '';
            
            for (let dia of dias) {
                html += `
                    <div class="col-md-4 col-sm-6">
                        <div class="horario-card">
                            <h4 class="text-center">${dia}</h4>
                `;
                
                // Turno Mañana
                let manana = horarios[dia]['Mañana'];
                html += `
                    <div class="horario-slot ${manana ? 'ocupado' : 'disponible'}">
                        <strong><i class="fas fa-sun"></i> Mañana</strong><br>
                `;
                if (manana) {
                    html += `
                        <small>${manana.hora_inicio} - ${manana.hora_fin}</small><br>
                        <small class="text-info"><i class="fas fa-user-md"></i> ${escapeHtml(manana.nombre_medico || 'Sin médico')}</small>
                    `;
                } else {
                    html += `<small class="text-muted">Disponible</small>`;
                }
                html += `
                        <div class="text-center mt-2">
                            <button class="btn btn-sm btn-primary btn-editar-horario" 
                                    data-dia="${dia}" data-turno="Mañana"
                                    data-hora-inicio="${manana ? manana.hora_inicio : '08:00'}"
                                    data-hora-fin="${manana ? manana.hora_fin : '12:00'}"
                                    data-medico-id="${manana && manana.id_medico ? manana.id_medico : ''}"
                                    data-medico-nombre="${manana ? escapeHtml(manana.nombre_medico || '') : ''}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </div>
                    </div>
                `;
                
                // Turno Tarde
                let tarde = horarios[dia]['Tarde'];
                html += `
                    <div class="horario-slot ${tarde ? 'ocupado' : 'disponible'} mt-2">
                        <strong><i class="fas fa-moon"></i> Tarde</strong><br>
                `;
                if (tarde) {
                    html += `
                        <small>${tarde.hora_inicio} - ${tarde.hora_fin}</small><br>
                        <small class="text-info"><i class="fas fa-user-md"></i> ${escapeHtml(tarde.nombre_medico || 'Sin médico')}</small>
                    `;
                } else {
                    html += `<small class="text-muted">Disponible</small>`;
                }
                html += `
                        <div class="text-center mt-2">
                            <button class="btn btn-sm btn-primary btn-editar-horario" 
                                    data-dia="${dia}" data-turno="Tarde"
                                    data-hora-inicio="${tarde ? tarde.hora_inicio : '13:00'}"
                                    data-hora-fin="${tarde ? tarde.hora_fin : '17:00'}"
                                    data-medico-id="${tarde && tarde.id_medico ? tarde.id_medico : ''}"
                                    data-medico-nombre="${tarde ? escapeHtml(tarde.nombre_medico || '') : ''}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </div>
                    </div>
                `;
                
                html += `</div></div>`;
            }
            
            $('#contenedor_horarios').html(html);
        },
        error: function() {
            $('#contenedor_horarios').html('<div class="col-12 text-center"><div class="alert alert-danger">Error al cargar horarios</div></div>');
        }
    });
}
// *****************Botón limpiar resultados desde el enlace********************* */
$(document).on('click', '#limpiarResultados', function(e) {
    e.preventDefault();
    $('#buscar_consultorio').val('');
    $('#resultado_busqueda').hide();
    $('#btnLimpiarBusqueda').hide();
    cargarConsultorios('');
    cargarEstadisticas();
});
//**              GUARDAR HORARIO************************************************ */
function guardarHorario() {
    let id_consultorio = $('#id_consultorio').val();
    let dia = $('#horario_dia').val();
    let turno = $('#horario_turno').val();
    let hora_inicio = $('#hora_inicio').val();
    let hora_fin = $('#hora_fin').val();
    let id_medico = $('#medico_asignado').val();
    
    // VALIDACIÓN 1: Horarios completos
    if (!hora_inicio || !hora_fin) {
        mostrarErrorHorario('Complete los horarios de inicio y fin');
        return;
    }
    
    // VALIDACIÓN 2: Hora fin mayor que hora inicio
    if (hora_inicio >= hora_fin) {
        mostrarErrorHorario('La hora de fin debe ser mayor que la hora de inicio');
        return;
    }
    
    // VALIDACIÓN 3: Validar formato de hora (opcional)
    const horaRegex = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
    if (!horaRegex.test(hora_inicio) || !horaRegex.test(hora_fin)) {
        mostrarErrorHorario('Formato de hora inválido. Use HH:MM (ej: 08:00, 14:30)');
        return;
    }
    
    // Confirmación si se va a sobrescribir un horario existente
    let slotExistente = $('.btn-editar-horario[data-dia="' + dia + '"][data-turno="' + turno + '"]');
    if (slotExistente.length && slotExistente.data('hora-inicio') !== hora_inicio) {
        if (!confirm('¿Está seguro de modificar este horario? Los cambios afectarán la programación actual.')) {
            return;
        }
    }
    
    // Mostrar loading
    let btn = $('#btnGuardarHorario');
    let originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: '../../controlador/ConsultorioController.php',
        type: 'POST',
        data: {
            funcion: 'guardar_horario',
            id_consultorio: id_consultorio,
            dia: dia,
            turno: turno,
            hora_inicio: hora_inicio,
            hora_fin: hora_fin,
            id_medico: id_medico
        },
        dataType: 'json',
        success: function(response) {
            if (response.resultado === 'guardado') {
                $('#modalHorario').modal('hide');
                mostrarExitoHorario('Horario guardado correctamente');
                cargarHorarios();
            } else if (response.resultado === 'error_horario') {
                mostrarErrorHorario(response.mensaje || 'La hora de fin debe ser mayor que la hora de inicio');
            } else if (response.resultado === 'error_duplicado') {
                mostrarErrorHorario(response.mensaje || 'El médico ya tiene un horario asignado en este día y turno');
            } else {
                mostrarErrorHorario('Error al guardar el horario');
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            mostrarErrorHorario('Error de conexión al guardar el horario');
        },
        complete: function() {
            btn.prop('disabled', false).html(originalText);
        }
    });
}

function mostrarErrorHorario(mensaje) {
    $('#errorMensaje').text(mensaje);
    $('#alertError').show();
    setTimeout(function() { $('#alertError').fadeOut(); }, 4000);
}

function mostrarExitoHorario(mensaje) {
    $('#alertExito').show();
    setTimeout(function() { $('#alertExito').fadeOut(); }, 3000);
}

// ==================== UTILIDADES ====================

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function mostrarAlerta(mensaje, tipo) {
    let alertDiv = tipo === 'success' ? '#alertExito' : '#alertError';
    if ($(alertDiv).length) {
        if (tipo === 'success') {
            $(alertDiv).html('<i class="fas fa-check-circle"></i> ' + mensaje);
        } else {
            $('#errorMensaje').text(mensaje);
        }
        $(alertDiv).show();
        setTimeout(function() { $(alertDiv).fadeOut(); }, 3000);
    } else {
        alert(mensaje);
    }
}

function mostrarMensaje(mensaje, tipo) {
    $('#mensaje_asignacion').removeClass('alert-success alert-danger')
        .addClass(tipo === 'success' ? 'alert-success' : 'alert-danger')
        .text(mensaje).show();
    setTimeout(function() { $('#mensaje_asignacion').fadeOut(); }, 2000);
}
