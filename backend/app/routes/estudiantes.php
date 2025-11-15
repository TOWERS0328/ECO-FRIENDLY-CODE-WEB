<?php

$routes = [
    "estudiante.registrar" => [
        "controller" => "EstudianteController",
        "method" => "registrar"
    ]
];

if (!isset($routes[$route])) {
    echo "Ruta no encontrada";
    exit;
}

$ctrl = $routes[$route];

require_once __DIR__ . '/../controllers/' . $ctrl['controller'] . '.php';

$controller = new $ctrl['controller'];
$controller->{$ctrl['method']}();

