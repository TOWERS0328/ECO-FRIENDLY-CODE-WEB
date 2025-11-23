<?php
global $routes;

// Rutas para CanjeController
$routes['canje.listarCarrito']      = ["CanjeController", "listarCarrito"];
$routes['canje.agregar']            = ["CanjeController", "agregarCarrito"];
$routes['canje.actualizar']         = ["CanjeController", "actualizarCantidad"];
$routes['canje.eliminarItem']       = ["CanjeController", "eliminarItem"];
$routes['canje.finalizar']          = ["CanjeController", "finalizarCanje"];
$routes['canje.historial']          = ["CanjeController", "listarHistorial"];
$routes['canje.listarAsistente']    = ["CanjeController", "listarCanjesAsistente"];
$routes['canje.obtener']            = ["CanjeController", "obtenerCanje"];       // trae detalles y premios
$routes['canje.entregar']           = ["CanjeController", "entregarCanjeAsistente"];
