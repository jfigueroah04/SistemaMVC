<?php

require_once __DIR__ . '/../config/conexion.php';

class ProductosModelo
{

    /* Obtener listado de productos */
    static public function mdlListarProductos()
    {
        try {
            $stmt = Conexion::conectar()->prepare('SELECT id, nombre, precio, stock, fecha_creacion FROM productos');
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en la consulta listar: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) {
                $stmt = null;
            }
        }
    }

    /* Crear producto */
    static public function mdlCrearProducto($datos)
    {
        try {
            // Validaciones básicas
            $nombre = trim($datos['nombre'] ?? '');
            $precio = is_numeric($datos['precio'] ?? null) ? $datos['precio'] : 0;
            $stock = intval($datos['stock'] ?? 0);

            $stmt = Conexion::conectar()->prepare('INSERT INTO productos (nombre, precio, stock) VALUES (:nombre, :precio, :stock)');
            $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindValue(':precio', $precio);
            $stmt->bindValue(':stock', $stock, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error crear producto: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    /* Obtener un producto por id */
    static public function mdlObtenerProducto($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare('SELECT id, nombre, precio, stock, fecha_creacion FROM productos WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obtener producto: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    /* Actualizar producto */
    static public function mdlActualizarProducto($datos)
    {
        try {
            $id = intval($datos['id'] ?? 0);
            $nombre = trim($datos['nombre'] ?? '');
            $precio = is_numeric($datos['precio'] ?? null) ? $datos['precio'] : 0;
            $stock = intval($datos['stock'] ?? 0);

            $stmt = Conexion::conectar()->prepare('UPDATE productos SET nombre = :nombre, precio = :precio, stock = :stock WHERE id = :id');
            $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindValue(':precio', $precio);
            $stmt->bindValue(':stock', $stock, PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizar producto: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    /* Eliminar producto */
    static public function mdlEliminarProducto($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare('DELETE FROM productos WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error eliminar producto: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

}
