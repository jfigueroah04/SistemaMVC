
<div class="d-flex justify-content-between mb-3">
    <h2>Modulo Ventas</h2>
    <button class="btn btn-success" id="btnCrearVenta"><i class="fa fa-plus me-1"></i> Crear Venta</button>
</div>

<div class="row">
    <table id="tbl_ventas" class="table table-hover" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>CLIENTE</th>
                <th>DETALLES</th>
                <th>TOTAL</th>
                <th>FECHA</th>
                <th>OPCIONES</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Drawer Ventas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="drawerVenta" aria-labelledby="drawerVentaLabel">
  <div class="offcanvas-header">
    <h5 id="drawerVentaLabel">Agregar Venta</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="formVenta">
      <input type="hidden" id="venta_id" name="id" value="">
            <div class="mb-3">
                <label class="form-label"><i class="fa fa-user"></i> Cliente</label>
                <select class="form-control" id="venta_cliente_id" name="cliente_id" required>
                    <option value="">-- Seleccione cliente --</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fa fa-list"></i> Detalles</label>
                <div class="table-responsive">
                    <table class="table table-sm" id="tablaDetalles">
                        <thead>
                            <tr>
                                <th>Producto ID</th>
                                <th>Cantidad</th>
                                <th>Precio unitario</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btnAgregarLinea"><i class="fa fa-plus me-1"></i> Agregar línea</button>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fa fa-receipt"></i> Total</label>
                <input type="number" step="0.01" class="form-control" id="venta_total" name="total" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fa fa-calendar"></i> Fecha</label>
                <input type="date" class="form-control" id="venta_fecha" name="fecha" required>
            </div>
      <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="offcanvas">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnGuardarVenta"><i class="fa fa-save me-1"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
    $(document).ready(function(){
        window.tablaVentas = $('#tbl_ventas').DataTable({
            ajax: {
                url: 'ajax/ventas.ajax.php',
                type: 'POST',
                dataType: 'json',
                dataSrc: '',
                data: { accion: 'listarVentas' }
            },
            columns: [ { data: 'id' }, { data: 'cliente' }, { data: 'detalle_html', render: function(d){ return d ? d : ''; } }, { data: 'total', render: function(d){ return 'Q ' + parseFloat(d).toFixed(2); } }, { data: 'fecha' },
                { data: null, render: function(data, type, row){
                    return `
                        <button class="action-btn gray me-1" title="Ver" onclick="verVenta(${row.id})"><i class="fa fa-eye"></i></button>
                        <button class="action-btn gray me-1" title="Editar" onclick="editarVenta(${row.id})"><i class="fa fa-pen"></i></button>
                        <button class="action-btn red" title="Eliminar" onclick="eliminarVenta(${row.id})"><i class="fa fa-trash"></i></button>
                    `;
                }}
            ],
            responsive:true,
            scrollY: '400px', scrollCollapse:true, paging:true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });
    });

    function editarVenta(id){
        $.post('ajax/ventas.ajax.php', { accion: 'obtenerVenta', id: id }, function(res){
            if(res && res.id){
                $('#drawerVentaLabel').text('Editar Venta');
                $('#venta_id').val(res.id);
                // Cargar clientes y seleccionar el correspondiente
                cargarClientesSelect(res.cliente_id);
                // Rellenar detalles
                $('#tablaDetalles tbody').empty();
                try {
                    var detalles = typeof res.detalles === 'string' ? JSON.parse(res.detalles) : res.detalles;
                    if (Array.isArray(detalles)) detalles.forEach(function(line){ agregarLineaDetalle(line); });
                } catch (e) { /* ignore parse errors */ }
                $('#venta_total').val(res.total).prop('disabled', false);
                $('#venta_fecha').val(res.fecha).prop('disabled', false);
                var d = new bootstrap.Offcanvas(document.getElementById('drawerVenta'));
                d.show();
            } else { toast.fire({ icon:'error', title:'No se pudo obtener la venta' }); }
        }, 'json').fail(function(){ toast.fire({ icon:'error', title:'Error en la petición' }); });
    }

    // Cargar clientes en el select
    function cargarClientesSelect(selectedId) {
        $.post('ajax/clientes.ajax.php', { accion: 'listarClientes' }, function(res){
            if (Array.isArray(res)){
                var $sel = $('#venta_cliente_id');
                $sel.empty().append('<option value="">-- Seleccione cliente --</option>');
                res.forEach(function(c){
                    var sel = c.id == selectedId ? 'selected' : '';
                    $sel.append(`<option value="${c.id}" ${sel}>${c.nombre}</option>`);
                });
            }
        }, 'json');
    }

    // Agregar línea a tabla de detalles
    function agregarLineaDetalle(line){
        line = line || { producto_id: '', cantidad: 1, precio_unitario: 0 };
        var $tr = $('<tr></tr>');
        var $tdProducto = $('<td></td>');
        var $select = $('<select class="form-control form-control-sm detalle-producto"></select>');
        $select.append('<option value="">-- Producto --</option>');
        // Cargar productos vía AJAX
        $.post('ajax/productos.ajax.php', { accion: 'listarProductos' }, function(res){
            if(Array.isArray(res)){
                res.forEach(function(p){
                    var txt = `${p.nombre} (Stock: ${p.stock})`;
                    var sel = p.id == line.producto_id ? 'selected' : '';
                    $select.append(`<option value="${p.id}" ${sel}>${txt}</option>`);
                });
            }
        }, 'json');
        $tdProducto.append($select);
        var $tdCantidad = $(`<td><input type="number" class="form-control form-control-sm detalle-cantidad" value="${line.cantidad}" min="1" /></td>`);
        var $tdPrecio = $(`<td><input type="number" step="0.01" class="form-control form-control-sm detalle-precio" value="${line.precio_unitario}" /></td>`);
        var $tdEliminar = $('<td><button type="button" class="btn btn-sm btn-danger btn-eliminar-linea"><i class="fa fa-trash"></i></button></td>');
        $tr.append($tdProducto, $tdCantidad, $tdPrecio, $tdEliminar);
        $('#tablaDetalles tbody').append($tr);
        recalcularTotal();
    }

    // Recalcular total a partir de detalles
    function recalcularTotal(){
        var total = 0;
        $('#tablaDetalles tbody tr').each(function(){
            var qty = parseFloat($(this).find('.detalle-cantidad').val()) || 0;
            var price = parseFloat($(this).find('.detalle-precio').val()) || 0;
            total += qty * price;
        });
        $('#venta_total').val(total.toFixed(2));
    }

    // Eventos para agregar/eliminar líneas y recalcular
    $(document).on('click', '#btnAgregarLinea', function(){ agregarLineaDetalle(); });
    $(document).on('click', '.btn-eliminar-linea', function(){ $(this).closest('tr').remove(); recalcularTotal(); });
    $(document).on('input', '.detalle-cantidad, .detalle-precio', function(){ recalcularTotal(); });

    // Eliminar venta
    function eliminarVenta(id){
        Swal.fire({ title:'¿Eliminar venta?', icon:'warning', showCancelButton:true, confirmButtonText:'Sí, eliminar' })
        .then(function(result){ if(result.isConfirmed){
            $.post('ajax/ventas.ajax.php', { accion:'eliminarVenta', id:id }, function(res){
                if(res.success){ toast.fire({ icon:'success', title:'Venta eliminada' }); window.tablaVentas.ajax.reload(); }
                else{ toast.fire({ icon:'error', title:'No se pudo eliminar' }); }
            }, 'json').fail(function(){ toast.fire({ icon:'error', title:'Error en la petición' }); });
        }});
    }

    $(document).on('click', '#btnGuardarVenta', function(){
        var id = $('#venta_id').val();
        var cliente_id = $('#venta_cliente_id').val();
        if(!cliente_id){ toast.fire({ icon:'warning', title:'Seleccione un cliente' }); return; }
        // serializar detalles
        var detalles = [];
        $('#tablaDetalles tbody tr').each(function(){
            var producto_id = parseInt($(this).find('.detalle-producto').val()) || 0;
            var cantidad = parseInt($(this).find('.detalle-cantidad').val()) || 0;
            var precio_unitario = parseFloat($(this).find('.detalle-precio').val()) || 0;
            if(producto_id && cantidad>0){ detalles.push({ producto_id: producto_id, cantidad: cantidad, precio_unitario: precio_unitario }); }
        });
        if(detalles.length===0){ toast.fire({ icon:'warning', title:'Agregue al menos una línea de detalle' }); return; }

        var datos = { cliente_id: cliente_id, detalles: JSON.stringify(detalles) };
        datos.accion = id ? 'actualizarVenta' : 'crearVenta'; if(id) datos.id = id;

        $.post('ajax/ventas.ajax.php', datos, function(res){
            if(res.success){ var drawer = bootstrap.Offcanvas.getInstance(document.getElementById('drawerVenta')); if(drawer) drawer.hide(); $('#formVenta')[0].reset(); $('#venta_id').val(''); $('#tablaDetalles tbody').empty(); window.tablaVentas.ajax.reload(); toast.fire({ icon:'success', title:'Guardado' }); }
            else toast.fire({ icon:'error', title:'Error al guardar' });
        }, 'json').fail(function(){ toast.fire({ icon:'error', title:'Error en la petición' }); });
    });

    document.getElementById('drawerVenta').addEventListener('hidden.bs.offcanvas', function(){
        $('#formVenta')[0].reset(); $('#venta_id').val(''); $('#drawerVentaLabel').text('Agregar Venta'); $('#venta_cliente_id').prop('disabled', false); $('#venta_total').prop('disabled', false); $('#venta_fecha').prop('disabled', false);
    });

    document.getElementById('btnCrearVenta').addEventListener('click', function(){
        $('#drawerVentaLabel').text('Crear Venta');
        // poblar select de clientes para crear nueva venta
        cargarClientesSelect();
        $('#venta_cliente_id').prop('disabled', false); $('#venta_total').prop('disabled', false); $('#venta_fecha').prop('disabled', false);
        var d = new bootstrap.Offcanvas(document.getElementById('drawerVenta'));
        d.show();
    });

    function verVenta(id){
        $.post('ajax/ventas.ajax.php', { accion:'obtenerVenta', id:id }, function(res){
            if(res && res.id){
                $('#drawerVentaLabel').text('Ver Venta');
                $('#venta_id').val(res.id);
                // cargar clientes y seleccionar, luego deshabilitar para solo lectura
                cargarClientesSelect(res.cliente_id);
                $('#venta_total').val(res.total).prop('disabled', true);
                $('#venta_fecha').val(res.fecha).prop('disabled', true);
                // cargar detalles
                $('#tablaDetalles tbody').empty();
                try {
                    var detalles = typeof res.detalles === 'string' ? JSON.parse(res.detalles) : res.detalles;
                    if (Array.isArray(detalles)) detalles.forEach(function(line){ agregarLineaDetalle(line); });
                } catch (e) { }
                // deshabilitar inputs de detalles
                $('#tablaDetalles tbody').find('input').prop('disabled', true);
                var d = new bootstrap.Offcanvas(document.getElementById('drawerVenta'));
                d.show();
            } else toast.fire({ icon:'error', title:'No se pudo obtener la venta' });
        }, 'json').fail(function(){ toast.fire({ icon:'error', title:'Error en la petición' }); });
    }
</script>
