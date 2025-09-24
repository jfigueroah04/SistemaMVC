<?php

require_once __DIR__ . '/../modelos/clientes.modelo.php';

class ClientesControlador
{
    public function index()
    {
        $vista = 'clientes';
        require 'plantilla.php';
    }

    static public function ctrListarClientes()
    {
        return ClientesModelo::mdlListarClientes();
    }

    static public function ctrCrearCliente($datos)
    {
        return ClientesModelo::mdlCrearCliente($datos);
    }

    static public function ctrObtenerCliente($id)
    {
        return ClientesModelo::mdlObtenerCliente($id);
    }

    static public function ctrActualizarCliente($datos)
    {
        return ClientesModelo::mdlActualizarCliente($datos);
    }

    static public function ctrEliminarCliente($id)
    {
        return ClientesModelo::mdlEliminarCliente($id);
    }

}
