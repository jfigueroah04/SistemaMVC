<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../controladores/ventas.controlador.php';
require_once __DIR__ . '/../modelos/ventas.modelo.php';
require_once __DIR__ . '/../modelos/productos.modelo.php';

try {
    $debug = (isset($_REQUEST['debug']) && $_REQUEST['debug'] == '1');

    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];

        switch ($accion) {
            case 'listarVentas':
                $listar = VentasControlador::ctrListarVentas();
                if ($listar === false || !is_array($listar)) {
                    if ($debug) echo json_encode(['error' => 'Error al listar ventas'], JSON_UNESCAPED_UNICODE);
                    else { http_response_code(500); echo json_encode([]); }
                    exit;
                }

                // Enriquecer cada venta con un texto legible de detalles: producto x cantidad (precio)
                $prodList = ProductosModelo::mdlListarProductos();
                $prodMap = [];
                if (is_array($prodList)) {
                    foreach ($prodList as $p) {
                        $prodMap[$p['id']] = $p['nombre'];
                    }
                }

                foreach ($listar as &$v) {
                    $detalleText = [];
                    $detalles = [];
                    if (!empty($v['detalles'])) {
                        $detalles = is_string($v['detalles']) ? json_decode($v['detalles'], true) : $v['detalles'];
                        if (!is_array($detalles)) $detalles = [];
                    }
                    foreach ($detalles as $line) {
                        $pid = isset($line['producto_id']) ? intval($line['producto_id']) : 0;
                        $qty = isset($line['cantidad']) ? intval($line['cantidad']) : 0;
                        $precio = isset($line['precio_unitario']) ? floatval($line['precio_unitario']) : null;
                        $pname = $prodMap[$pid] ?? ('#' . $pid);
                        if ($precio !== null) $detalleText[] = $pname . ' x' . $qty . ' (Q ' . number_format($precio,2) . ')';
                        else $detalleText[] = $pname . ' x' . $qty;
                    }
                    $v['detalle_text'] = implode(', ', $detalleText);
                    // HTML legible (cada artículo en nueva línea)
                    $v['detalle_html'] = implode('<br/>', array_map(function($s){ return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }, $detalleText));
                }

                echo json_encode($listar, JSON_UNESCAPED_UNICODE);
                break;

            case 'crearVenta':
                // Esperamos cliente_id y detalles (JSON string o array serializado)
                $datos = ['cliente_id' => intval($_POST['cliente_id'] ?? 0), 'detalles' => $_POST['detalles'] ?? '[]'];
                $res = VentasControlador::ctrCrearVenta($datos);
                echo json_encode(['success' => (bool)$res]);
                break;

            case 'obtenerVenta':
                $id = intval($_POST['id'] ?? 0);
                $v = VentasControlador::ctrObtenerVenta($id);
                echo json_encode($v, JSON_UNESCAPED_UNICODE);
                break;

            case 'actualizarVenta':
                $datos = ['id' => intval($_POST['id'] ?? 0), 'cliente_id' => intval($_POST['cliente_id'] ?? 0), 'detalles' => $_POST['detalles'] ?? '[]'];
                $res = VentasControlador::ctrActualizarVenta($datos);
                echo json_encode(['success' => (bool)$res]);
                break;

            case 'eliminarVenta':
                $id = intval($_POST['id'] ?? 0);
                $res = VentasControlador::ctrEliminarVenta($id);
                echo json_encode(['success' => (bool)$res]);
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Accion no reconocida']);
                break;
        }
    } else {
        echo json_encode([]);
    }
} catch (Throwable $e) {
    error_log('Exception en ajax/ventas.ajax.php: ' . $e->getMessage());
    if (!empty($debug)) echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    else { http_response_code(500); echo json_encode(['success' => false, 'error' => 'Error interno']); }
}
