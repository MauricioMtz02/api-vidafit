<?php

namespace Classes;

use stdClass;

Class Response{

    public $codeStatus;
    public $msg;
    public $data;

    public function res($codeStatus = "", $msg = ""){
        $this->codeStatus = $codeStatus;
        $this->msg = $msg != "" ? $msg : "Internal Server ERROR";

        $this->toPrint();
    }

    public function res200($data, $meta = [], $msg = "ok"){
        $this->codeStatus = 200;
        $this->msg = $msg;

        if(empty($meta)){
            $meta = new stdClass;
        }

        header('Content-Type: application/json');
        http_response_code($this->codeStatus);
        echo json_encode([
            "status" => $this->codeStatus,
            "msg" => $this->msg,
            "meta" => $meta,
            "data" => $data
        ]);
    }

    public function res204($msg = ""){
        $this->codeStatus = 204;
        $this->msg = $msg != "" ? $msg : "NO CONTENT";
        $this->toPrint();
    }

    public function res400($msg = ""){
        $this->codeStatus = 400;
        $this->msg = $msg != "" ? $msg : "BAD REQUEST";
        $this->toPrint();
    }

    public function res401($msg = ""){
        $this->codeStatus = 401;
        $this->msg = $msg != "" ? $msg : "UNAUTHORIZED";
        $this->toPrint();
    }

    public function res403($msg = ""){
        $this->codeStatus = 403;
        $this->msg = $msg != "" ? $msg : "FORBIDDEN";
        $this->toPrint();
    }

    public function res405($msg = ""){
        $this->codeStatus = 405;
        $this->msg = $msg != "" ? $msg : "METHOD NOT ALLOWED";
        $this->toPrint();
    }

    public function res500($msg = ""){
        $this->codeStatus = 500;
        $this->msg = $msg != "" ? $msg : "Internal Server ERROR";
        $this->toPrint();
    }

    protected function toPrint(){
        header('Content-Type: application/json');
        http_response_code($this->codeStatus);
        echo json_encode([
            "status" => $this->codeStatus,
            "msg" => $this->msg
        ]);
    }
}