<?php
global $routes;

$routes['empresa.listar']        = ["EmpresaController", "listar"];
$routes['empresa.listarActivas'] = ["EmpresaController", "listarActivas"];
$routes['empresa.registrar']     = ["EmpresaController", "registrar"];
$routes['empresa.actualizar']    = ["EmpresaController", "actualizar"];
$routes['empresa.obtener']       = ["EmpresaController", "obtener"];
