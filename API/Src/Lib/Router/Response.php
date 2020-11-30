<?php

  namespace Src\Lib\Router;

  class Response {
    private $body;

    public function __construct () {
      header_remove();
      header("Content-Type: application/json");
    }

    public function setBody ($rawBody) {
      $this->body = json_encode($rawBody);
    }

    public function setBodyRaw ($body) {
      $this->body = $body;
    }

    public function getBody () {
      return $this->body;
    }

    public function setStatus ($statusCode) {
      http_response_code($statusCode);
    }

    public function send () {//não vai literalmente mandar a response, vai somente incrementá-la com o body, a response será enviada assim que o todo o script estiver completo (desse modo, é possível que middlewares acrescentem na resposta sem afetar as callbacks)
      echo $this->body;
    }

    public function abortWithError ($statusCode, $errorMsg) {//sai automaticamente com um erro
      $this->setStatus($statusCode);
      $this->setBody($errorMsg);
      $this->send();
    }
  }

?>