<?php

use Src\Error;
use Src\Error\Extensions;
namespace Src\Lib\Router;

class Router {
  public $routes = [];

  public function __construct ($routes = []) {
    $this->routes = $routes;
  }

  public function addRoute (Route $route) {
    array_push($this->routes, $route);
  }

  public function start ($action, $uri) {
    Filters::_filterAction($action);
    $found = false;
    foreach ($this->routes as $routeObj) {//percorre todas as rotas
      $route = $routeObj->getURI();
      if (preg_match($route, $uri)) {//se o padrão da rota atual corresponder com a URI
        try {
          $routeObj->run($action);
          $found = true;
        } catch (NotFoundException $e) {
          (new Response())->setStatus(404)->send();//caso a rota não exista, envia uma response 404 vazia
        } catch (SysException $e) {
          (new Response())->setStatus(500)->send();//caso ocorra alguma merda, envia uma response 500 vazia
        }
      }
    }
    if (!$found) {
      (new Response())->setStatus(404)->send();//caso a rota não exista, envia uma response 404 vazia
    }
  }
}

?>