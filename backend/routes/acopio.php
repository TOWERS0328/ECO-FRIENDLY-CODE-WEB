<?php
global $routes;

$routes['canasta.listar']          = ["AcopioController", "listarCanasta"];
$routes['canasta.agregar']         = ["AcopioController", "agregarCanasta"];
$routes['canasta.eliminarItem']    = ["AcopioController", "eliminarItem"];
$routes['canasta.actualizar']      = ["AcopioController", "actualizarCantidad"];
$routes['canasta.finalizar']       = ["AcopioController", "finalizarAcopio"];
$routes['acopio.listar']           = ["AcopioController", "listarAcopios"]; 
$routes['acopio.actualizarEstado'] = ["AcopioController", "actualizarEstado"];     
$routes['acopio.listarAcopiosAsistente'] = ["AcopioController", "listarAcopiosAsistente"];
$routes['acopio.listarAcopiosEstudiante']  = ["AcopioController", "listarAcopiosEstudiante"];
