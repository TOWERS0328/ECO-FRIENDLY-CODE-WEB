<?php
global $routes;

// Si usas controlador AcopioController que tiene agregarCanasta, listarCanasta, etc:
$routes['canasta.listar']          = ["AcopioController", "listarCanasta"];
$routes['canasta.agregar']         = ["AcopioController", "agregarCanasta"];
$routes['canasta.eliminarItem']    = ["AcopioController", "eliminarItem"];      // si lo implementas
$routes['canasta.actualizar']      = ["AcopioController", "actualizarCantidad"]; // si lo implementas
$routes['canasta.finalizar']       = ["AcopioController", "finalizarAcopio"];
$routes['acopio.actualizarEstado'] = ["AcopioController", "actualizarEstado"];



