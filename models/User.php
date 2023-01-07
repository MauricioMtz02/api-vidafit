<?php

namespace Model;

class User extends ActiveRecord{
    //Base de datos
    protected static $tabla = 'accounts';
    protected static $columnasDB = ['id', 'username', 'email', 'password', 'phone_number', 'admin', 'token', 'status', 'created_at', 'last_update'];

    protected static $pkColumn = 'id';
    protected static $stateColumn = 'status';

    // Proteger columnas
    public static $protectedColumns = ['id', 'password', 'admin', 'token', 'status', 'created_at', 'last_update'];

    // Atributos mapeados en base de datos
    public $id;
    public $username;
    public $email;
    public $phone_number;

    public $jwt;
    public $admin;
    public $status;
    public $created_at;
    public $last_update;

    //Protegido
    protected $password;
    protected $token;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;

        $this->username = $args['username'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->phone_number = $args['phone_number'] ?? '';
        
        $this->status = $args['status'] ?? 1;
        $this->admin = $args['admin'] ?? 0;
    }

    // Setter y getter
    public function setPassword($password){
        $this->password = $password;
    }

    public function getPassword(){
        return $this->password;
    }

    public function setAdmin($admin){
        $this->admin = $admin;
    }

    public function getAdmin(){
        return $this->admin;
    }

    public function setToken($token){
        $this->token = $token;
    }

    public function getToken(){
        return $this->token;
    }

    //Mensajes de validación para la creación de una cuenta
    public function validarNuevaCuenta(){
        if(!$this->username){
            self::$alertas['error'][] = 'El Username es Obligatorio';
        }

        if(!$this->email){
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }

        return self::$alertas;
    }

    public function validarNuevoIngreso($password = ""){
        if($this->password === ''){
            self::$alertas['error'][] = 'El Password es obligatorio';
        } else if(strlen($this->password) < 6){
            self::$alertas['error'][] = 'El password debe tener al menos 6 caracteres';
        }

        if($this->password != $password){
            self::$alertas['error'][] = 'Los password no coinciden';
        }

        return self::$alertas;
    }

    public function validarLogin(){
        if(!$this->email){
            self::$alertas['error'][] = 'El Email es obligatorio';
        }

        if(!$this->password){
            self::$alertas['error'][] = 'El Password es obligatorio';
        }

        return self::$alertas;
    }

    public function validarEmail(){
        if(!$this->email){
            self::$alertas['error'][] = 'El Email es obligatorio';
        }

        return self::$alertas;
    }

    public function validarPassword(){
        if(!$this->password){
            self::$alertas['error'][] = 'El Password es obligatorio';
        }

        if(strlen($this->password) < 6){
            self::$alertas['error'][] = 'El password debe tener al menos 6 caracteres';
        }

        return self::$alertas;
    }

    //Revisa si el usuario ya existe
    public function existeEmail(){
        $query = "SELECT * FROM " . self::$tabla . " WHERE email = '" . $this->email . "' LIMIT 1";

        $resultado = self::$db->query($query);
        
        if($resultado->num_rows){
            self::$alertas['error'][] = "El email ya esta asociado a una cuenta";
        }
        
        return self::$alertas;
    }

    //Revisa si el usuario ya existe
    public function existeUsername(){
        $query = "SELECT * FROM " . self::$tabla . " WHERE username = '" . $this->username . "' LIMIT 1";

        $resultado = self::$db->query($query);
        
        if($resultado->num_rows){
            self::$alertas['error'][] = "El Nombre de Usuario ya esta asociado a una cuenta";
        }
        
        return self::$alertas;
    }

    public function hashPassword(){
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function crearToken(){
        $this->token = uniqid();
    }

    public function comprobarPassword($password){
        $resultado = password_verify($password, $this->password);

        if(!$resultado){
            self::$alertas['error'][] = 'Password incorrecto';
        } else{
            return true;
        }
    }

    public function comprobarToken(){
        $auth =  static::where("token", $this->token);

        if($auth){
            foreach($auth as $columna=>$valor){
                $this->$columna = $valor;
            }
        }

        return $auth ? true : false;
    }
}