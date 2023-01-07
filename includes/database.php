<?php

use Classes\Response;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db = mysqli_connect($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);
} catch (mysqli_sql_exception $e) {
    $res = new Response;
    $res->res500("Error en el servidor, no se pudo establecer una conexión a la base de datos");
    exit;
}