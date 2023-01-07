<?php

function debuguear($variable) : string {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// Escapa / Sanitizar el HTML
function s($html) : string {
    $s = htmlspecialchars($html);
    return $s;
}

// funcion que revisa que el usuario este autenticado
function isAuth(){
    if(!isset($_SESSION['auth'])){
        header("Location: /auth");
    }
}

// funcion que revisa que el usuario sea administrador
function isAdmin(){
    if(!isset($_SESSION['admin'])){
        header("Location: /auth");
    }
}

//Convierte la informacion de la consulta en formato UTF8
function convertirUTF8($item){
    if(!mb_detect_encoding($item, "utf-8", true)){
        $item = utf8_encode($item);
    }

    return $item;
}

function segmentar($array, $inicio, $fin){
    $finalArray = [];
    $bandera= 0;
    foreach($array as $i=>$row){
        if($i >= $inicio){
            if($bandera <= $fin){
                $finalArray[] = $row;
                $bandera++;
            }
        }
    }

    return $finalArray;
}

//Genera un texto para la consulta SELECT con las columnas de cada modelo
function generateColumnsText($table, $columns){
    
    $columnsText = "";
    $longArray = count($columns) - 1;
    foreach($columns as $i=>$column){
        $i != $longArray ? $columnsText.= $table.".$column, " : $columnsText.= $table.".$column ";
        
    }   
    
    return $columnsText;
}

function generateWhereText($whereArray, $table = ''){
    empty($whereArray) ? $text = " WHERE 1=1 " : $text = " WHERE ";

    $longArray = count($whereArray) - 1;
    $i = 0;
    foreach($whereArray as $filter=>$value){
        $value = s($value);
        $filter = s($filter);

        if($table === ''){
            $text .= $filter." = '".$value."'";
        } else{
            $text .= $table.".".$filter." = '".$value."'";
        }

        
        $i != $longArray ? $text.= ' AND ' : $text.= ' ';
        $i++;
    }

    return $text;
}

function obtenerIdUrl($posicion = 3) {
    //Variable que nos permite una ruta dinamica a traves de un ID
    $arrayUrl = explode("/", $_SERVER['REQUEST_URI']);
    return intval($arrayUrl[$posicion] ?? 0);
}

function calcularInicio($page, $limit){
    return !$page > 1 ? $page - 1 : (($page - 1) * $limit);
}

//Crea una maqueta con informacion de la consulta para mostrar el resultado
function generateInfo($rows = [], $count = 0, $page = 0, $limit = 0){
    return [
        "count" => intval($count),
        "totalPages" => ceil($count / $limit),
        "page" => $page,
        "rows" => $rows
    ];
}

// Genera una contraseña aleatoria
function random_password($length = 10)  
{    
  $pass = substr(md5(rand()),0,$length);  
  return($pass); // devuelve el password   
} 

function is_json($str){
    return ((is_string($str) && (is_object(json_decode($str)) || is_array(json_decode($str))))) ? true : false;
}

function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

// get access token from header
function getBearerToken() {
    $headers = getAuthorizationHeader();

    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function getParam($param){
    $value = null;

    if(isset($_GET[$param]) && !empty($_GET[$param])){
        $value = $_GET[$param];
        unset($_GET[$param]);
    }

    return $value;
}

function generateUrl($string){
    $string = strtolower($string); 
    $string = str_replace("/", "", $string);
    $string = str_replace("%", "", $string);
    $string = str_replace("$", "", $string);
    $string = str_replace("?", "", $string);
    $string = str_replace("=", "", $string);
    $string = str_replace("&", "", $string);
    
    return preg_replace('/\s+/', '-', $string); 
}