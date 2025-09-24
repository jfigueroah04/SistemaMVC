
<?php
require_once __DIR__ . '/../modelos/productos.modelo.php';
require_once __DIR__ . '/../modelos/clientes.modelo.php';
require_once __DIR__ . '/../modelos/ventas.modelo.php';

$productos = ProductosModelo::mdlListarProductos();
$totalProductos = is_array($productos) ? count($productos) : 0;

$clientes = ClientesModelo::mdlListarClientes();
$totalClientes = is_array($clientes) ? count($clientes) : 0;

$ventas = VentasModelo::mdlListarVentas();
$totalVentas = is_array($ventas) ? count($ventas) : 0;

// Suma total de ventas (Q)
$totalMonto = 0;
if (is_array($ventas)) {
    foreach ($ventas as $v) {
        $totalMonto += floatval($v['total'] ?? 0);
    }
}
?>
<h2>Dashboard</h2>
<div class="dashboard-cards">
    <div class="card">
        <h3>Productos</h3>
        <p><?php echo $totalProductos; ?></p>
    </div>
    <div class="card">
        <h3>Clientes</h3>
        <p><?php echo $totalClientes; ?></p>
    </div>
    <div class="card">
        <h3>Ventas</h3>
        <p><?php echo $totalVentas; ?></p>
    </div>
    <div class="card">
        <h3>Monto Total</h3>
        <p>Q <?php echo number_format($totalMonto, 2); ?></p>
    </div>
</div>
