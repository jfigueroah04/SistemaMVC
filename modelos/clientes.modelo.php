<?php

require_once __DIR__ . '/../config/conexion.php';

class ClientesModelo
{
    static public function mdlListarClientes()
    {
        try {
            $stmt = Conexion::conectar()->prepare('SELECT id, nombre, email, telefono, direccion, fecha_creacion FROM clientes');
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error listar clientes: ' . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    static public function mdlCrearCliente($datos)
    {
        try {
            $nombre = trim($datos['nombre'] ?? '');
            $email = trim($datos['email'] ?? null);
            $telefono = trim($datos['telefono'] ?? null);
            $direccion = trim($datos['direccion'] ?? null);

            $stmt = Conexion::conectar()->prepare('INSERT INTO clientes (nombre, email, telefono, direccion) VALUES (:nombre, :email, :telefono, :direccion)');
            $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':telefono', $telefono, PDO::PARAM_STR);
            $stmt->bindValue(':direccion', $direccion, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error crear cliente: ' . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    static public function mdlObtenerCliente($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare('SELECT id, nombre, email, telefono, direccion, fecha_creacion FROM clientes WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtener cliente: ' . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    static public function mdlActualizarCliente($datos)
    {
        try {
            $id = intval($datos['id'] ?? 0);
            $nombre = trim($datos['nombre'] ?? '');
            $email = trim($datos['email'] ?? null);
            $telefono = trim($datos['telefono'] ?? null);
            $direccion = trim($datos['direccion'] ?? null);

            $stmt = Conexion::conectar()->prepare('UPDATE clientes SET nombre = :nombre, email = :email, telefono = :telefono, direccion = :direccion WHERE id = :id');
            $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':telefono', $telefono, PDO::PARAM_STR);
            $stmt->bindValue(':direccion', $direccion, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error actualizar cliente: ' . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    static public function mdlEliminarCliente($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare('DELETE FROM clientes WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error eliminar cliente: ' . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

}
