
<div class="d-flex justify-content-between mb-3">
    <h2>Nodulo Clientes</h2>
    <button class="btn btn-success" id="btnCrearCliente"><i class="fa fa-plus me-1"></i> Crear Cliente</button>
</div>

<div class="row">
    <table id="tbl_clientes" class="table table-hover" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>NOMBRE</th>
                <th>EMAIL</th>
                <th>TELEFONO</th>
                <th>OPCIONES</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Drawer Clientes -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="drawerCliente" aria-labelledby="drawerClienteLabel">
  <div class="offcanvas-header">
    <h5 id="drawerClienteLabel">Agregar Cliente</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="formCliente">
      <input type="hidden" id="cliente_id" name="id" value="">
      <div class="mb-3">
        <label class="form-label"><i class="fa fa-user"></i> Nombre</label>
        <input type="text" class="form-control" id="cliente_nombre" name="nombre" required>
      </div>
      <div class="mb-3">
        <label class="form-label"><i class="fa fa-envelope"></i> Email</label>
        <input type="email" class="form-control" id="cliente_email" name="email" required>
      </div>
      <div class="mb-3">
        <label class="form-label"><i class="fa fa-phone"></i> Teléfono</label>
        <input type="text" class="form-control" id="cliente_telefono" name="telefono">
      </div>
      <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="offcanvas">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnGuardarCliente"><i class="fa fa-save me-1"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
    $(document).ready(function(){
        window.tablaClientes = $('#tbl_clientes').DataTable({
            ajax: {
                url: 'ajax/clientes.ajax.php',
                type: 'POST',
                dataType: 'json',
                dataSrc: '',
                data: { accion: 'listarClientes' }
            },
            columns: [ { data: 'id' }, { data: 'nombre' }, { data: 'email' }, { data: 'telefono' },
                { data: null, render: function(data, type, row){
                    return `
                        <button class="action-btn gray me-1" title="Ver" onclick="verCliente(${row.id})"><i class="fa fa-eye"></i></button>
                        <button class="action-btn gray me-1" title="Editar" onclick="editarCliente(${row.id})"><i class="fa fa-pen"></i></button>
                        <button class="action-btn red" title="Eliminar" onclick="eliminarCliente(${row.id})"><i class="fa fa-trash"></i></button>
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

    function editarCliente(id){
        $.post('ajax/clientes.ajax.php', { accion: 'obtenerCliente', id: id }, function(res){
            if(res && res.id){
                $('#drawerClienteLabel').text('Editar Cliente');
                $('#cliente_id').val(res.id);
                $('#cliente_nombre').val(res.nombre).prop('disabled', false);
                $('#cliente_email').val(res.email).prop('disabled', false);
                $('#cliente_telefono').val(res.telefono).prop('disabled', false);
                var d = new bootstrap.Offcanvas(document.getElementById('drawerCliente'));
                d.show();
            } else { toast.fire({ icon:'error', title:'No se pudo obtener el cliente' }); }
        }, 'json').fail(function(){ toast.fire({ icon:'error', title:'Error en la petición' }); });
    }

    function eliminarCliente(id){
        Swal.fire({ title:'¿Eliminar cliente?', icon:'warning', showCancelButton:true, confirmButtonText:'Sí, eliminar' })
        .then(function(result){ if(result.isConfirmed){
            $.post('ajax/clientes.ajax.php', { accion:'eliminarCliente', id:id }, function(res){
                if(res.success){ toast.fire({ icon:'success', title:'Cliente eliminado' }); window.tablaClientes.ajax.reload(); }
                else{ toast.fire({ icon:'error', title:'No se pudo eliminar' }); }
            }, 'json').fail(function(){ toast.fire({ icon:'error', title:'Error en la petición' }); });
        }});
    }

    $(document).on('click', '#btnGuardarCliente', function(){
        var id = $('#cliente_id').val();
        var datos = { nombre: $('#cliente_nombre').val(), email: $('#cliente_email').val(), telefono: $('#cliente_telefono').val() };
        datos.accion = id ? 'actualizarCliente' : 'crearCliente';
        if(id) datos.id = id;
        $.post('ajax/clientes.ajax.php', datos, function(res){
            if(res.success){ var drawer = bootstrap.Offcanvas.getInstance(document.getElementById('drawerCliente')); if(drawer) drawer.hide(); $('#formCliente')[0].reset(); $('#cliente_id').val(''); window.tablaClientes.ajax.reload(); toast.fire({ icon:'success', title:'Guardado' }); }
            else toast.fire({ icon:'error', title:'Error al guardar' });
        }, 'json').fail(function(){ toast.fire({ icon:'error', title:'Error en la petición' }); });
    });

    document.getElementById('drawerCliente').addEventListener('hidden.bs.offcanvas', function(){
        $('#formCliente')[0].reset(); $('#cliente_id').val(''); $('#drawerClienteLabel').text('Agregar Cliente'); $('#cliente_nombre').prop('disabled', false); $('#cliente_email').prop('disabled', false); $('#cliente_telefono').prop('disabled', false);
    });

    document.getElementById('btnCrearCliente').addEventListener('click', function(){ $('#drawerClienteLabel').text('Crear Cliente'); $('#cliente_nombre').prop('disabled', false); $('#cliente_email').prop('disabled', false); $('#cliente_telefono').prop('disabled', false); var d = new bootstrap.Offcanvas(document.getElementById('drawerCliente')); d.show(); });

    function verCliente(id){
        $.post('ajax/clientes.ajax.php', { accion:'obtenerCliente', id:id }, function(res){
            if(res && res.id){ $('#drawerClienteLabel').text('Ver Cliente'); $('#cliente_id').val(res.id); $('#cliente_nombre').val(res.nombre).prop('disabled', true); $('#cliente_email').val(res.email).prop('disabled', true); $('#cliente_telefono').val(res.telefono).prop('disabled', true); var d = new bootstrap.Offcanvas(document.getElementById('drawerCliente')); d.show(); }
            else toast.fire({ icon:'error', title:'No se pudo obtener el cliente' });
        }, 'json').fail(function(){ toast.fire({ icon:'error', title:'Error en la petición' }); });
    }
</script>
