<?php

namespace Src\Error\Extensions;

use Src\Error;

class EmailExistsException extends Error\InvalidActionException {//classe que irá representar os erros de emails já existentes
  public function __construct($code = 0, Exception $previous = null) {
    parent::__construct("Email already in use", $code, $previous);
  }
}

?>