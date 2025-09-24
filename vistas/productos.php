
</div>
    <div class="d-flex justify-content-between mb-3">
    <h2>Inventario Productos</h2>
    <button class="btn btn-success" id="btnCrearProducto">
        <i class="fa fa-plus me-1"></i> Crear Producto
    </button>
</div>

<div class="row">
    <table id="tbl_productos" class="table table-hover" style="width:100%">
        <thead class="bg-info">
            <tr style="font-size: 15px;">
                <th>ID</th>
                <th>NOMBRE</th>
                <th>PRECIO VENTA</th>
                <th>STOCK</th>
                <th>FECHA CREACIÓN</th>
                <th>OPCIONES</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>



<script>
    $(document).ready(function() {

        window.tablaProductos = $('#tbl_productos').DataTable({
            ajax: {
                async: true,
                url: 'ajax/productos.ajax.php',
                type: 'POST',
                dataType: 'json',
                dataSrc: "",
                data: {
                    accion: 'listarProductos'
                }
            },
            columns: [{
                    "data": "id"
                },
                {
                    "data": "nombre"
                },
                {
                    "data": "precio",
                    "render": function(data, type, row) {
                        return "Q " + parseFloat(data).toFixed(2);
                    }
                },
                {
                    "data": "stock"
                },
                {
                    "data": "fecha_creacion"
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return `
                            <button class="action-btn gray me-1" title="Ver" onclick="ver(${row.id})"><i class="fa fa-eye"></i></button>
                            <button class="action-btn gray me-1" title="Editar" onclick="editar(${row.id})"><i class="fa fa-pen"></i></button>
                            <button class="action-btn red" title="Eliminar" onclick="eliminar(${row.id})"><i class="fa fa-trash"></i></button>
                        `;
                    }
                }
            ],
            scrollY: "400px",
            scrollX: true,
            scrollCollapse: true,
            autoWidth: false,
            paging: true,
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });



    });

    function editar(id) {
        // obtener datos del producto y mostrar en drawer
        $.post('ajax/productos.ajax.php', { accion: 'obtenerProducto', id: id }, function(response) {
            if (response && response.id) {
                $('#drawerProductoLabel').text('Editar Producto');
                $('#producto_id').val(response.id);
                $('#producto_nombre').val(response.nombre).prop('disabled', false);
                $('#producto_precio').val(response.precio).prop('disabled', false);
                $('#producto_stock').val(response.stock).prop('disabled', false);
                var drawerEl = document.getElementById('drawerProducto');
                var drawer = new bootstrap.Offcanvas(drawerEl);
                drawer.show();
            } else {
                toast.fire({ icon: 'error', title: 'No se pudo obtener el producto' });
            }
        }, 'json').fail(function() { toast.fire({ icon: 'error', title: 'Error en la petición' }); });
    }

    function eliminar(id) {
        Swal.fire({
            title: '¿Desea eliminar este producto?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function(result){
            if (result.isConfirmed) {
                $.post('ajax/productos.ajax.php', { accion: 'eliminarProducto', id: id }, function(res) {
                    if (res.success) {
                        toast.fire({ icon: 'success', title: 'Producto eliminado' });
                        window.tablaProductos.ajax.reload();
                    } else {
                        toast.fire({ icon: 'error', title: 'No se pudo eliminar' });
                    }
                }, 'json').fail(function() { toast.fire({ icon: 'error', title: 'Error en la petición' }); });
            }
        });
    }
</script>


<!-- Offcanvas drawer para crear/editar/ver producto -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="drawerProducto" aria-labelledby="drawerProductoLabel">
    <div class="offcanvas-header">
        <h5 id="drawerProductoLabel">Agregar Producto</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="formProducto">
            <input type="hidden" id="producto_id" name="id" value="">
            <div class="mb-3">
                <label for="producto_nombre" class="form-label"><i class="fa fa-tag"></i> Nombre</label>
                <input type="text" class="form-control" id="producto_nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="producto_precio" class="form-label"><i class="fa fa-dollar-sign"></i> Precio</label>
                <input type="number" step="0.01" class="form-control" id="producto_precio" name="precio" required>
            </div>
            <div class="mb-3">
                <label for="producto_stock" class="form-label"><i class="fa fa-cubes"></i> Stock</label>
                <input type="number" class="form-control" id="producto_stock" name="stock" required>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="offcanvas">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarProducto"><i class="fa fa-save me-1"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Manejar envío del formulario (crear o actualizar)
    $(document).on('click', '#btnGuardarProducto', function() {
        var id = $('#producto_id').val();
        var datos = {
            nombre: $('#producto_nombre').val(),
            precio: $('#producto_precio').val(),
            stock: $('#producto_stock').val()
        };

        if (id) {
            datos.id = id;
            datos.accion = 'actualizarProducto';
        } else {
            datos.accion = 'crearProducto';
        }

        $.post('ajax/productos.ajax.php', datos, function(res) {
            if (res.success) {
                var drawerEl = document.getElementById('drawerProducto');
                var drawer = bootstrap.Offcanvas.getInstance(drawerEl);
                if (drawer) drawer.hide();
                // limpiar formulario
                $('#formProducto')[0].reset();
                $('#producto_id').val('');
                window.tablaProductos.ajax.reload();
                toast.fire({ icon: 'success', title: 'Guardado correctamente' });
            } else {
                toast.fire({ icon: 'error', title: 'Error al guardar' });
            }
        }, 'json').fail(function() { toast.fire({ icon: 'error', title: 'Error en la petición' }); });
    });

    // Limpiar drawer al cerrarlo
    var drawerElCleanup = document.getElementById('drawerProducto');
    drawerElCleanup.addEventListener('hidden.bs.offcanvas', function () {
        $('#formProducto')[0].reset();
        $('#producto_id').val('');
        $('#drawerProductoLabel').text('Agregar Producto');
        // reactivar campos
        $('#producto_nombre').prop('disabled', false);
        $('#producto_precio').prop('disabled', false);
        $('#producto_stock').prop('disabled', false);
    });

    // Abrir drawer para crear
    document.getElementById('btnCrearProducto').addEventListener('click', function(){
        $('#drawerProductoLabel').text('Crear Producto');
        $('#producto_nombre').prop('disabled', false);
        $('#producto_precio').prop('disabled', false);
        $('#producto_stock').prop('disabled', false);
        var drawerEl = document.getElementById('drawerProducto');
        var drawer = new bootstrap.Offcanvas(drawerEl);
        drawer.show();
    });

    // Ver producto (solo lectura)
    function ver(id) {
        $.post('ajax/productos.ajax.php', { accion: 'obtenerProducto', id: id }, function(response) {
            if (response && response.id) {
                $('#drawerProductoLabel').text('Ver Producto');
                $('#producto_id').val(response.id);
                $('#producto_nombre').val(response.nombre).prop('disabled', true);
                $('#producto_precio').val(response.precio).prop('disabled', true);
                $('#producto_stock').val(response.stock).prop('disabled', true);
                var drawerEl = document.getElementById('drawerProducto');
                var drawer = new bootstrap.Offcanvas(drawerEl);
                drawer.show();
            } else {
                toast.fire({ icon: 'error', title: 'No se pudo obtener el producto' });
            }
        }, 'json').fail(function() { toast.fire({ icon: 'error', title: 'Error en la petición' }); });
    }
</script>