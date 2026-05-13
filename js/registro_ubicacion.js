$(document).ready(function() {
    // Cargar estados al cargar la página
    cargarEstados();
    
    // Eventos de cambio
    $(document).on('change', '#estado', function() {
        let id_estado = $(this).val();
        if (id_estado) {
            cargarCiudades(id_estado);
            cargarMunicipiosPorEstado(id_estado);
        } else {
            resetUbicacion();
        }
    });
    
    $(document).on('change', '#ciudad', function() {
        let ciudad_nombre = $('#ciudad option:selected').text();
        // Solo actualizamos la vista previa si existe
        if ($('#preview_ciudad').length) {
            $('#preview_ciudad').text(ciudad_nombre || 'Ciudad');
        }
    });
    
    $(document).on('change', '#municipio', function() {
        let id_municipio = $(this).val();
        if (id_municipio) {
            cargarParroquias(id_municipio);
        } else {
            $('#parroquia').html('<option value="">Primero seleccione un municipio...</option>').prop('disabled', true);
        }
    });
    
    // Funciones
    function cargarEstados() {
        $.ajax({
            url: '../controlador/ConsultorioController.php',
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
                // Fallback: datos estáticos
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
    }
    
    function cargarCiudades(id_estado) {
        if (!id_estado) {
            $('#ciudad').html('<option value="">Seleccione un estado primero...</option>').prop('disabled', true);
            return;
        }
        
        $('#ciudad').html('<option value="">Cargando ciudades...</option>').prop('disabled', true);
        
        $.ajax({
            url: '../controlador/ConsultorioController.php',
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
            error: function(xhr) {
                console.error('Error cargando ciudades:', xhr.responseText);
                $('#ciudad').html('<option value="">Error al cargar ciudades</option>').prop('disabled', false);
            }
        });
    }
    
    function cargarMunicipiosPorEstado(id_estado) {
        if (!id_estado) {
            $('#municipio').html('<option value="">Seleccione un estado primero...</option>').prop('disabled', true);
            $('#parroquia').html('<option value="">Primero seleccione un municipio...</option>').prop('disabled', true);
            return;
        }
        
        $('#municipio').html('<option value="">Cargando municipios...</option>').prop('disabled', true);
        
        $.ajax({
            url: '../controlador/ConsultorioController.php',
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
            url: '../controlador/ConsultorioController.php',
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
    
    function resetUbicacion() {
        $('#ciudad').html('<option value="">Primero seleccione un estado...</option>').prop('disabled', true);
        $('#municipio').html('<option value="">Primero seleccione una ciudad...</option>').prop('disabled', true);
        $('#parroquia').html('<option value="">Primero seleccione un municipio...</option>').prop('disabled', true);
    }
});
