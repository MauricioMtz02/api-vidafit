<?php

namespace Controllers;

use Classes\Response;

abstract class Controller{
    protected static $model = null;
    protected static $searchColumn = '';

    public static function all(){
        $res = new Response;

        $data = [];
        $meta = [];

        try {
            if(isset($_GET['search']) && !empty($_GET['search'])){
                $search = $_GET['search'] ?? '0';
                unset($_GET['search']);
                unset($_GET['limit']);
                unset($_GET['page']);

                $data = static::$model::like(static::$searchColumn, $search, $_GET);
            } else{
                // Identificamos parametros de configuración
                if($param = getParam("limit")){
                    // Tranformamos a int
                    $param = intval($param);
                    // Validamos que sea un numero
                    $param && static::$model::setLimit($param);
                }

                if($param = getParam("page")){
                    // Tranformamos a int
                    $param = intval($param);
                    // Validamos que sea un numero
                    $param && static::$model::setPage($param);
                }

                $where = $_GET;

                // Total de registros
                $rows = static::$model::count($where);

                // Preparamos la meta data
                $meta = [
                    "rows" => $rows,
                    "page" => static::$model::getPage(),
                    "pageSize" => ceil($rows / static::$model::getLimit()),
                    "limit" => static::$model::getLimit()
                ];

                $data = static::$model::all($where);
            }

            $res->res200($data, $meta);
        } catch (\Throwable $th) {
            $res->res500();
        }
    }

    public static function find(){
        $res = new Response;

        $id = obtenerIdUrl();

        try {
            $row = static::$model::find($id);
            $res->res200($row);
        } catch (\Throwable $th) {
            $res->res500();
        }

    }

    public static function save(){
        $res = new Response;

        // A traves de POST
        if(empty($_POST)){
            $res->res400();
            exit;
        }

        $register = new static::$model($_POST);

        // Validamos si existe un ID para saber si es necesario crear o actualizar
        if($register->id){
            // Actualizar
            $registerPrevious = static::$model::find($register->id);

            // Sincronizamos con las columnas que no seran modificadas
            foreach($registerPrevious as $column=>$value){
                if(empty($register->$column)){
                    $register->$column = $value;
                }

                // Setear valores de columnas protegidas
                foreach(static::$model::$protectedColumns as $protectedColumn){
                    if($column === $protectedColumn){
                        $register->$column = $value;
                    }
                }
            }
        } else{
            // Nuevo Registro
            $register->created_at = date('Y-m-d H:i:s');
        }

        $register->last_update = date('Y-m-d H:i:s');

        try {
            $result = $register->guardar();

            if(empty($register->id)){
                if(!$result['result']){
                    $res->res500();
                    exit;
                }

                $register->id = $result['id'];
            }

            $res->res200($register);
        } catch (\Throwable $th) {
            $res->res500();
        }

    }

    public static function count(){
        $res = new Response;

        unset($_GET['limit']);
        unset($_GET['page']);

        $where = $_GET;

        try {
            $res->res200(static::$model::count($where));
        } catch (\Throwable $th) {
            $res->res500();
        }
    }

    public static function status(){
        $res = new Response;

        // Validar Metodo
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $res->res405();
            exit;
        }

        // Validar usuario admin


        // Validar ID
        $id = intval(obtenerIdUrl(3));

        if(!$id){
            $res->res400();
            exit;
        }

        // Obtener el registro
        $register = static::$model::find($id);

        // Validar que haya un registro
        if(!$register){
            $res->res204();
            exit;
        }

        if($register->status === '1'){
            $register->status = 0;
        } else{
            $register->status = 1;
        }

        try {
            $result = $register->guardar();
            $res->res200($result);
        } catch (\Throwable $th) {
            $res->res500();
        }
    }
}
