<?php


class ProductosControlador
{

    public function index()
    {

        $vista = 'productos';
        require 'plantilla.php';
    }


    static public function ctrListarProductos()
    {
        $listarProductos = ProductosModelo::mdlListarProductos();

        return $listarProductos;
    }

    /* Crear producto */
    static public function ctrCrearProducto($datos)
    {
        return ProductosModelo::mdlCrearProducto($datos);
    }

    /* Obtener producto */
    static public function ctrObtenerProducto($id)
    {
        return ProductosModelo::mdlObtenerProducto($id);
    }

    /* Actualizar producto */
    static public function ctrActualizarProducto($datos)
    {
        return ProductosModelo::mdlActualizarProducto($datos);
    }

    /* Eliminar producto */
    static public function ctrEliminarProducto($id)
    {
        return ProductosModelo::mdlEliminarProducto($id);
    }

}
