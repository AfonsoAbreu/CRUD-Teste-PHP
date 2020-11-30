<?php

  namespace Src\Lib\Router;

  class Request {
    public $body;//sem necessidades para getters e setters
    public $params;

    public function __construct () {
      $this->body = json_decode(file_get_contents("php://input"), true);
      $this->params = $_GET;
    }
  }

?>