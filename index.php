<?php


$controlador = isset($_GET['c']) ? $_GET['c'] : 'dashboard';
$accion = isset($_GET['a']) ? $_GET['a'] : 'index';

// Normalizar: el archivo de controlador se espera en minúsculas
$archivoControlador = "controladores/" . strtolower($controlador) . ".controlador.php";

if (file_exists($archivoControlador)) {
    require_once $archivoControlador;
    // Construir nombre de clase en formato CamelCase como en los controladores
    $nombreClase = ucfirst(strtolower($controlador)) . "Controlador";

    if (class_exists($nombreClase)) {
        $obj = new $nombreClase();

        if (method_exists($obj, $accion)) {
            $obj->$accion();
        } else {
            echo "La acción $accion no existe.";
        }
    } else {
        echo "La clase $nombreClase no existe.";
    }
} else {
    echo "El controlador $archivoControlador no existe.";
}
