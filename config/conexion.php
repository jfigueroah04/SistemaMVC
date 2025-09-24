<?php

class Conexion
{

    static public function conectar()
    {
        try {
            $dsn = "mysql:host=localhost;dbname=tienda_mvc;charset=utf8mb4";
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            );

            // Nota: actualizar la contraseña si tu instalación de MySQL la requiere
            $conn = new PDO($dsn, "root", "1234", $options);
            return $conn;
        } catch (PDOException $e) {
            echo 'Falló la conexión: ' . $e->getMessage();
        }
    }
}
