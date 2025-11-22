<?php
global $routes;

// Rutas de canje de premios
$routes['canje.listar']           = ["CanjeController", "listarCarrito"];
$routes['canje.agregar']          = ["CanjeController", "agregarCarrito"];
$routes['canje.actualizar']       = ["CanjeController", "actualizarCantidad"];
$routes['canje.eliminarItem']     = ["CanjeController", "eliminarItem"];
$routes['canje.finalizar']        = ["CanjeController", "finalizarCanje"];
