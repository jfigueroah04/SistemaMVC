<?php

require_once __DIR__ . '/../config/conexion.php';

class VentasModelo
{
    static public function mdlListarVentas()
    {
        try {
            $sql = 'SELECT v.id, v.cliente_id, IFNULL(c.nombre, "") AS cliente, v.detalles, v.total, v.fecha '
                 . 'FROM ventas v LEFT JOIN clientes c ON v.cliente_id = c.id';
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error listar ventas: ' . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    static public function mdlCrearVenta($datos)
    {
        try {
            $db = Conexion::conectar();
            $db->beginTransaction();

            $cliente_id = intval($datos['cliente_id'] ?? 0);
            $detalles = $datos['detalles'] ?? '[]';

            // Validar y descontar stock por cada línea
            $lineas = json_decode($detalles, true);
            if (!is_array($lineas)) $lineas = [];

            foreach ($lineas as $l) {
                $pid = intval($l['producto_id'] ?? 0);
                $qty = intval($l['cantidad'] ?? 0);
                if ($pid <= 0 || $qty <= 0) continue;

                // Verificar stock actual
                $stmtCheck = $db->prepare('SELECT stock FROM productos WHERE id = :id FOR UPDATE');
                $stmtCheck->bindValue(':id', $pid, PDO::PARAM_INT);
                $stmtCheck->execute();
                $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    throw new Exception('Producto no existe: ' . $pid);
                }
                $stockActual = intval($row['stock']);
                if ($stockActual < $qty) {
                    throw new Exception('Stock insuficiente para producto ' . $pid);
                }

                // Descontar stock
                $stmtUpd = $db->prepare('UPDATE productos SET stock = stock - :q WHERE id = :id');
                $stmtUpd->bindValue(':q', $qty, PDO::PARAM_INT);
                $stmtUpd->bindValue(':id', $pid, PDO::PARAM_INT);
                $stmtUpd->execute();
            }

            // Insertar la venta
            $stmt = $db->prepare('INSERT INTO ventas (cliente_id, detalles) VALUES (:cliente_id, :detalles)');
            $stmt->bindValue(':cliente_id', $cliente_id, PDO::PARAM_INT);
            $stmt->bindValue(':detalles', $detalles, PDO::PARAM_STR);
            $stmt->execute();

            $db->commit();
            return true;
        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            error_log('Error crear venta: ' . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    static public function mdlObtenerVenta($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare('SELECT id, cliente_id, detalles, total, fecha FROM ventas WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtener venta: ' . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    static public function mdlActualizarVenta($datos)
    {
        try {
            $db = Conexion::conectar();
            $db->beginTransaction();

            $id = intval($datos['id'] ?? 0);
            $cliente_id = intval($datos['cliente_id'] ?? 0);
            $detalles = $datos['detalles'] ?? '[]';

            // Obtener venta previa y devolver stock
            $stmtPrev = $db->prepare('SELECT detalles FROM ventas WHERE id = :id FOR UPDATE');
            $stmtPrev->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtPrev->execute();
            $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);
            if ($prev) {
                $linPrev = json_decode($prev['detalles'], true);
                if (is_array($linPrev)) {
                    foreach ($linPrev as $l) {
                        $pid = intval($l['producto_id'] ?? 0);
                        $qty = intval($l['cantidad'] ?? 0);
                        if ($pid>0 && $qty>0) {
                            $db->prepare('UPDATE productos SET stock = stock + :q WHERE id = :id')->execute([':q'=>$qty, ':id'=>$pid]);
                        }
                    }
                }
            }

            // Aplicar nuevos detalles (validar stock y descontar)
            $lineas = json_decode($detalles, true);
            if (!is_array($lineas)) $lineas = [];
            foreach ($lineas as $l) {
                $pid = intval($l['producto_id'] ?? 0);
                $qty = intval($l['cantidad'] ?? 0);
                if ($pid <= 0 || $qty <= 0) continue;

                $stmtCheck = $db->prepare('SELECT stock FROM productos WHERE id = :id FOR UPDATE');
                $stmtCheck->bindValue(':id', $pid, PDO::PARAM_INT);
                $stmtCheck->execute();
                $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                if (!$row) throw new Exception('Producto no existe: ' . $pid);
                $stockActual = intval($row['stock']);
                if ($stockActual < $qty) throw new Exception('Stock insuficiente para producto ' . $pid);

                $db->prepare('UPDATE productos SET stock = stock - :q WHERE id = :id')->execute([':q'=>$qty, ':id'=>$pid]);
            }

            // Actualizar la venta
            $stmt = $db->prepare('UPDATE ventas SET cliente_id = :cliente_id, detalles = :detalles WHERE id = :id');
            $stmt->bindValue(':cliente_id', $cliente_id, PDO::PARAM_INT);
            $stmt->bindValue(':detalles', $detalles, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $db->commit();
            return true;
        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            error_log('Error actualizar venta: ' . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    static public function mdlEliminarVenta($id)
    {
        try {
            $db = Conexion::conectar();
            $db->beginTransaction();

            // Obtener detalles para devolver stock
            $stmtGet = $db->prepare('SELECT detalles FROM ventas WHERE id = :id FOR UPDATE');
            $stmtGet->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtGet->execute();
            $row = $stmtGet->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $lin = json_decode($row['detalles'], true);
                if (is_array($lin)) {
                    foreach ($lin as $l) {
                        $pid = intval($l['producto_id'] ?? 0);
                        $qty = intval($l['cantidad'] ?? 0);
                        if ($pid>0 && $qty>0) {
                            $db->prepare('UPDATE productos SET stock = stock + :q WHERE id = :id')->execute([':q'=>$qty, ':id'=>$pid]);
                        }
                    }
                }
            }

            $stmt = $db->prepare('DELETE FROM ventas WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $db->commit();
            return true;
        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            error_log('Error eliminar venta: ' . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

}
