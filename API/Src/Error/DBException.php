<?php

namespace Src\Error;

class DBException {//só estpu fazendo isso pois a classe de exceções do mysqli só tem menbros privados
  public function __construct ($parent) {
    $workaround = new \ReflectionClass($parent);
    $sqlstate = $workaround->getProperty("sqlstate");
    $sqlstate->setAccessible(true);
    $this->sqlstate = $sqlstate->getValue($parent);
    $message = $workaround->getProperty("message");
    $message->setAccessible(true);
    $this->message = $message->getValue($parent);
    $code = $workaround->getProperty("code");
    $code->setAccessible(true);
    $this->code = $code->getValue($parent);
    $file = $workaround->getProperty("file");
    $file->setAccessible(true);
    $this->file = $file->getValue($parent);
    $line = $workaround->getProperty("line");
    $line->setAccessible(true);
    $this->line = $line->getValue($parent);
  }
}

?>