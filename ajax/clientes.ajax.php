<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../controladores/clientes.controlador.php';
require_once __DIR__ . '/../modelos/clientes.modelo.php';

try {
    $debug = (isset($_REQUEST['debug']) && $_REQUEST['debug'] == '1');

    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];

        switch ($accion) {
            case 'listarClientes':
                $listar = ClientesControlador::ctrListarClientes();
                if ($listar === false || !is_array($listar)) {
                    if ($debug) echo json_encode(['error' => 'Error al listar clientes'], JSON_UNESCAPED_UNICODE);
                    else { http_response_code(500); echo json_encode([]); }
                    exit;
                }
                echo json_encode($listar, JSON_UNESCAPED_UNICODE);
                break;

            case 'crearCliente':
                $datos = ['nombre' => $_POST['nombre'] ?? '', 'email' => $_POST['email'] ?? '', 'telefono' => $_POST['telefono'] ?? ''];
                $res = ClientesControlador::ctrCrearCliente($datos);
                echo json_encode(['success' => (bool)$res]);
                break;

            case 'obtenerCliente':
                $id = intval($_POST['id'] ?? 0);
                $cli = ClientesControlador::ctrObtenerCliente($id);
                echo json_encode($cli, JSON_UNESCAPED_UNICODE);
                break;

            case 'actualizarCliente':
                $datos = ['id' => intval($_POST['id'] ?? 0), 'nombre' => $_POST['nombre'] ?? '', 'email' => $_POST['email'] ?? '', 'telefono' => $_POST['telefono'] ?? ''];
                $res = ClientesControlador::ctrActualizarCliente($datos);
                echo json_encode(['success' => (bool)$res]);
                break;

            case 'eliminarCliente':
                $id = intval($_POST['id'] ?? 0);
                $res = ClientesControlador::ctrEliminarCliente($id);
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
    error_log('Exception en ajax/clientes.ajax.php: ' . $e->getMessage());
    if (!empty($debug)) echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    else { http_response_code(500); echo json_encode(['success' => false, 'error' => 'Error interno']); }
}
