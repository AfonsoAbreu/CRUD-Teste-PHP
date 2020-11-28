<?php

namespace Src\Error;

class InvalidActionException extends \Exception {//classe que irá representar os erros do usuário
  public function __construct($message, $code = 0, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}

?>