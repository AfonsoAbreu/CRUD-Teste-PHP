<?php

namespace Src\Error;

class SysException extends \Exception {//classe que irá representar os erros do sistema
  public function __construct($message, $code = 0, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}

?>