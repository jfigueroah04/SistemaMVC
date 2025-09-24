<?php
require_once __DIR__ . '/../config/conexion.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Prueba directa: SELECT * FROM productos</h2>";

try {
    $db = Conexion::conectar();
    $stmt = $db->prepare('SELECT id, nombre, precio, stock, fecha_creacion FROM productos');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Filas obtenidas: " . count($rows) . "</h3>";

    if (count($rows) > 0) {
        echo '<table border="1" cellpadding="6" cellspacing="0">';
        echo '<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Fecha</th></tr>';
        foreach ($rows as $r) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($r['id']) . '</td>';
            echo '<td>' . htmlspecialchars($r['nombre']) . '</td>';
            echo '<td>' . htmlspecialchars($r['precio']) . '</td>';
            echo '<td>' . htmlspecialchars($r['stock']) . '</td>';
            echo '<td>' . htmlspecialchars($r['fecha_creacion']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>No se obtuvieron filas.</p>';
    }

    echo '<h3>Salida JSON (cruda)</h3>';
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Error en la consulta: " . $e->getMessage();
}
