<?php

namespace Src\Error\Extensions;

use Src\Error;

class VehicleExistsException extends Error\InvalidActionException {//classe que irá representar os erros de placas de carros já existentes
  public function __construct($code = 0, Exception $previous = null) {
    parent::__construct("A vehicle with this same number plate already exists", $code, $previous);
  }
}

?>