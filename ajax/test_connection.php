<?php
require_once __DIR__ . '/../config/conexion.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $conn = Conexion::conectar();
    $stmt = $conn->query('SELECT COUNT(*) AS total FROM productos');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'total_productos' => $row['total']]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
