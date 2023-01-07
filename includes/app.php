<?php 
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->safeLoad();

require 'cors.php';
require 'helpers.php';
require 'database.php';

date_default_timezone_set('America/Mexico_City');

// Conectarnos a la base de datos
use Model\ActiveRecord;
ActiveRecord::setDB($db);