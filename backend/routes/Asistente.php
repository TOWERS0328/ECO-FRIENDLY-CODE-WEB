<?php
global $routes;

// 🌿 Rutas para Ayudantes
$routes['asistente.registrar'] = ["AsistenteController","registrar"];
$routes['asistente.listar'] = ["AsistenteController","listar"];
$routes['asistente.actualizar'] = ["AsistenteController","actualizar"];
$routes['asistente.restablecer'] = ["AsistenteController","restablecer"];
$routes['asistente.obtener'] = ["AsistenteController","obtenerPorId"];

