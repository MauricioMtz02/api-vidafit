<?php
namespace Model;
abstract class ActiveRecord {

    // Base DE DATOS
    protected static $db;
    protected static $tabla = '';
    protected static $columnasDB = [];

    public static $protectedColumns = [];

    protected static $pkColumn = '';
    protected static $stateColumn = '';
    protected static $last_updateColumn = 'last_update';

    // Alertas y Mensajes
    protected static $alertas = [];

    //Pagina por defecto
    protected static $page = 1;
    //Limite por consulta
    protected static $limit = 500;

    // Definir la conexión a la BD - includes/database.php
    public static function setDB($database) {
        self::$db = $database;
    }

    // Obtiene la pagina actual
    public static function getPage(){
        return static::$page;
    }

    // Setea la pagina a consultar
    public static function setPage($page){
        static::$page = $page;
    }

    // Obtiene el limite de registros
    public static function getLimit(){
        return static::$limit;
    }
    
    // Define el limite por consulta
    public static function setLimit($limit){
        static::$limit = $limit;
    }

    public static function setAlerta($tipo, $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
    }

    // Validación
    public static function getAlertas() {
        return static::$alertas;
    }

    public function validar() {
        static::$alertas = [];
        return static::$alertas;
    }

    // Consulta SQL para crear un objeto en Memoria
    public static function consultarSQL($query) {
        // Consultar la base de datos
        $resultado = self::$db->query($query);

        // Iterar los resultados
        $array = [];
        while($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }

        // liberar la memoria
        $resultado->free();

        // retornar los resultados
        return $array;
    }

    // Crea el objeto en memoria que es igual al de la BD
    protected static function crearObjeto($registro) {
        $obj = new static;
        $obj->sincronizar($registro);

        return $obj;
    }

    // Identificar y unir los atributos de la BD
    public function atributos() {
        $atributos = [];
        foreach(static::$columnasDB as $column) {
            if($column === static::$pkColumn){
                continue;
            }
            $atributos[$column] = $this->$column;
        }

        return $atributos;
    }

    // Sanitizar los datos antes de guardarlos en la BD
    public function sanitizarAtributos() {
        $atributos = $this->atributos();
        $sanitizado = [];
        foreach($atributos as $key => $value ) {
            if(!is_null($value)){
                $sanitizado[$key] = self::$db->escape_string($value);
            }
        }

        return $sanitizado;
    }

    // Sincroniza BD con Objetos en memoria
    public function sincronizar($args=[]) {
        foreach($args as $key => $value) {
          if(property_exists($this, $key) && !is_null($value)) {
              $this->$key = convertirUTF8($value);
              if(is_json($this->$key)){
                $this->$key = json_decode($this->$key);
              }
          }
        }
    }

    // Registros - CRUD
    public function guardar() {
        $resultado = '';
        if(!is_null($this->id)) {
            // actualizar
            $resultado = $this->actualizar();
        } else {
            // Creando un nuevo registro
            $resultado = $this->crear();
        }
        return $resultado;
    }

    // Todos los registros
    public static function all($where = []) {
        // Calcular inicio para la consulta LIMIT
        $inicio = calcularInicio(static::$page, static::$limit);

        $query = "SELECT * FROM ".static::$tabla.generateWhereText($where, static::$tabla)." LIMIT $inicio, ".static::$limit;

        return self::consultarSQL($query);
    }

    // Todos los registros
    public static function like($column, $value, $where = []) {
        $query = "SELECT * FROM ".static::$tabla.generateWhereText($where, static::$tabla)." AND ".static::$tabla.".".$column." LIKE '%".s($value)."%'";

        return self::consultarSQL($query);
    }

    // Busca un registro por su id
    public static function find($id) {
        $query = "SELECT * FROM " . static::$tabla  ." WHERE ".static::$pkColumn." = ${id}";
        $resultado = self::consultarSQL($query);
        return array_shift( $resultado ) ;
    }

    // Busca un registro por su id
    public static function where($columna, $valor) {
        $query = "SELECT * FROM " . static::$tabla  ." WHERE ${columna} = '${valor}'";
        $resultado = self::consultarSQL($query);
        return array_shift( $resultado ) ;
    }

    //ConsultaLibre de SQL //consulta plana de sql
    public static function SQL($consulta) {
        $query = $consulta;
        return self::consultarSQL($query);
    }

    public static function count($where = [], $sql = '1 = 1'){
        $query = "SELECT COUNT(*) as count FROM ".static::$tabla.generateWhereText($where, static::$tabla)." AND ".$sql;

        $res = self::$db->query($query);
        $count = 0;

        while($row = $res->fetch_assoc()){
            $count = intval($row['count'] ?? 0);
        }

        return $count;
    }

    // crea un nuevo registro
    public function crear() {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        // Insertar en la base de datos
        $query = " INSERT INTO " . static::$tabla . " ( ";
        $query .= join(', ', array_keys($atributos));
        $query .= " ) VALUES ('"; 
        $query .= join("', '", array_values($atributos));
        $query .= "') ";
        
        // Resultado de la consulta
        $resultado = self::$db->query($query);
        return [
           'result' =>  $resultado,
           'id' => self::$db->insert_id
        ];
    }

    // Actualizar el registro
    public function actualizar() {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        // Iterar para ir agregando cada campo de la BD
        $valores = [];
        foreach($atributos as $key => $value) {
            $valores[] = "{$key}='{$value}'";
        }

        // Consulta SQL
        $query = "UPDATE " . static::$tabla ." SET ";
        $query .=  join(', ', $valores );
        $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "' ";
        $query .= " LIMIT 1 ";
        // debuguear($query);

        // Actualizar BD
        return self::$db->query($query);
    }

    // Eliminar un Registro por su ID
    public function eliminar() {
        $query = "DELETE FROM "  . static::$tabla . " WHERE id = " . self::$db->escape_string($this->id) . " LIMIT 1";
        return self::$db->query($query);
    }

}