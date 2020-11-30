<?php

namespace Src\Controller;

use Src\Model\MakeModel;
use Src\Lib\Router\Request;
use Src\Lib\Router\Response;

class MakeController {
  public static function getMakes (Request $request, Response $response) {
    $response->setBody(MakeModel::Retrieve());
    $response->setStatus(200);
    $response->send();
  }
}

?>