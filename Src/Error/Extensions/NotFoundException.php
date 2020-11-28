<?php

namespace Src\Error\Extensions;

use Src\Error;

class NotFoundException extends Error\InvalidActionException {//classe que irá representar os erros de emails já existentes
  public function __construct($code = 0, Exception $previous = null) {
    parent::__construct("Requested route not found", $code, $previous);
  }
}

?>