<?php

require_once __DIR__ . '/../modelos/ventas.modelo.php';

class VentasControlador
{
    public function index()
    {
        $vista = 'ventas';
        require 'plantilla.php';
    }

    static public function ctrListarVentas()
    {
        return VentasModelo::mdlListarVentas();
    }

    static public function ctrCrearVenta($datos)
    {
        return VentasModelo::mdlCrearVenta($datos);
    }

    static public function ctrObtenerVenta($id)
    {
        return VentasModelo::mdlObtenerVenta($id);
    }

    static public function ctrActualizarVenta($datos)
    {
        return VentasModelo::mdlActualizarVenta($datos);
    }

    static public function ctrEliminarVenta($id)
    {
        return VentasModelo::mdlEliminarVenta($id);
    }

}
