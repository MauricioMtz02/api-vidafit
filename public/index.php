<?php 

require_once __DIR__ . '/../includes/app.php';

use MVC\Router;

$router = new Router();

$id = obtenerIdUrl();

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();