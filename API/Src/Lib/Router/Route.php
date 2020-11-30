<?php

use Src\Lib\Router;
use Src\Error;
namespace Src\Lib\Router;

class Route {
  private $actions = [];//["POST": ["Src\blablabla\classe::metodo", "Src\blablabla\classe::metodo2"]...]
  private $uri;// a uri em questão

  public function __construct ($uri) {
    $this->setURI($uri);
  }

  public function setURI ($uri) {
    Filters::_filterURI($uri);// "Cars?make=Fiat" => "/cars?make=fiat"
    $this->uri = '/^' . str_replace('/', '\/', $uri) . '$/';//trata a uri em um formato de Regex, que poderá ser usada como pesquisa
  }

  public function getURI () {
    return $this->uri;
  }

  public function addCallback ($action, $callback) {
    Filters::_filterAction($action);
    if (array_key_exists($action, $this->actions) === false) {
      $this->actions[$action] = [];
    }
    array_push($this->actions[$action], $callback);
  }

  public function addMiddleware ($action, $callback) {//não sei se é gambiarra ou não, mas somente adiciona uma callback normal no começo do array 
    Filters::_filterAction($action);
    if (array_key_exists($action, $this->actions) === false) {
      $this->actions[$action] = [];
    }
    array_unshift($this->actions[$action], $callback);
  }

  public function run ($action) {
    Filters::_filterAction($action);
    if (!isset($this->actions[$action])) {// joga uma exceção caso a ação não exista
      throw new NotFoundException();
    }
    $request = new Request();
    $response = new Response();
    foreach ($this->actions[$action] as $func) {
      try {//tenta executar a função
        $continue = call_user_func($func, $request, $response);//existe uma classe para request e outra para response, são responsáveis por lidar com as requisições/respostas http em JSON
      } catch (Exception $e) {//se ocorrer alguma exceção, traduz ela para uma SysException
        break;
        throw new SysException();
      }
      if ($continue === false) {//se uma função retornar false, termina o loop
        break;
      }
    }
  }
}

?>