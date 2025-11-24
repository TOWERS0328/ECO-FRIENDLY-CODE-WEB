<?php
global $routes;

$routes['asistente.registrar'] = ["AsistenteController","registrar"];
$routes['asistente.listar'] = ["AsistenteController","listar"];
$routes['asistente.actualizar'] = ["AsistenteController","actualizar"];
$routes['asistente.restablecer'] = ["AsistenteController","restablecer"];
$routes['asistente.obtener'] = ["AsistenteController","obtenerPorId"];
$routes['asistente.actualizarPerfil'] = ["AsistenteController","actualizarPerfilAsistente"];
$routes['asistente.obtenerPerfil'] = ["AsistenteController","obtenerPerfil"];
$routes['asistente.resumenDashboard'] = ["AsistenteController","resumenDashboard"];

