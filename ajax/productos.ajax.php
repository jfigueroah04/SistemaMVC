<?php

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../controladores/productos.controlador.php';
require_once __DIR__ . '/../modelos/productos.modelo.php';

// Manejo sencillo y seguro de acciones AJAX
try {
    // Flag de depuración: si se envía debug=1 (POST o GET), devolvemos detalles de error para ayudar a debug
    $debug = (isset($_REQUEST['debug']) && $_REQUEST['debug'] == '1');

    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];

        switch ($accion) {
            case 'listarProductos':
                $listarProductos = ProductosControlador::ctrListarProductos();
                if ($listarProductos === false || !is_array($listarProductos)) {
                    error_log('ajax/productos.ajax.php: Error al listar productos');
                    if ($debug) {
                        // devolver un objeto con error para depuración (mantener 200 para que DataTables muestre la respuesta)
                        echo json_encode(['error' => 'Error al listar productos', 'detail' => 'Revise logs en el servidor'], JSON_UNESCAPED_UNICODE);
                    } else {
                        http_response_code(500);
                        echo json_encode([]);
                    }
                    exit;
                }
                echo json_encode($listarProductos, JSON_UNESCAPED_UNICODE);
                break;

            case 'crearProducto':
                $datos = [
                    'nombre' => $_POST['nombre'] ?? '',
                    'precio' => $_POST['precio'] ?? 0,
                    'stock' => $_POST['stock'] ?? 0,
                ];
                $resultado = ProductosControlador::ctrCrearProducto($datos);
                echo json_encode(['success' => (bool)$resultado]);
                break;

            case 'obtenerProducto':
                $id = intval($_POST['id'] ?? 0);
                $producto = ProductosControlador::ctrObtenerProducto($id);
                echo json_encode($producto, JSON_UNESCAPED_UNICODE);
                break;

            case 'actualizarProducto':
                $datos = [
                    'id' => intval($_POST['id'] ?? 0),
                    'nombre' => $_POST['nombre'] ?? '',
                    'precio' => $_POST['precio'] ?? 0,
                    'stock' => $_POST['stock'] ?? 0,
                ];
                $resultado = ProductosControlador::ctrActualizarProducto($datos);
                echo json_encode(['success' => (bool)$resultado]);
                break;

            case 'eliminarProducto':
                $id = intval($_POST['id'] ?? 0);
                $resultado = ProductosControlador::ctrEliminarProducto($id);
                echo json_encode(['success' => (bool)$resultado]);
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Accion no reconocida']);
                break;
        }
    } else {
        // Si se accede sin acción, devolver un array vacío (DataTables espera un array)
        echo json_encode([]);
    }
} catch (Throwable $e) {
    error_log('Exception en ajax/productos.ajax.php: ' . $e->getMessage());
    if (!empty($debug)) {
        // En modo debug devolvemos detalles (NO usar en producción)
        echo json_encode(['success' => false, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno']);
    }
}