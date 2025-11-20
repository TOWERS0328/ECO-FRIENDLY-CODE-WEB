<?php
global $routes;

$routes['residuo.listar']    = ["ResiduoController", "listar"];
$routes['residuo.crear']     = ["ResiduoController", "crear"];
$routes['residuo.actualizar'] = ["ResiduoController", "actualizar"];
$routes['residuo.obtener']   = ["ResiduoController", "obtener"];
$routes['residuo.catalogo']  = ["ResiduoController", "listarActivos"];
