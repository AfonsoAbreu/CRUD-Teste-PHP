<?php

use Src\Lib\Router\Router;
use Src\Lib\Router\Route;

$routes = [];
//TODO: inserir todos os controllers aqui
$usuario = new Route("/user");
$usuario->addCallback("POST", ["Src\Controller\UserController", "createUser"]);
array_push($routes, $usuario);

$auth = new Route("/auth");
$auth->addCallback("POST", ["Src\Controller\UserController", "login"]);
array_push($routes, $auth);

$router = new Router($routes);

//pega a uri da requisição
$self = isset($_SERVER['PHP_SELF']) ? str_replace('index.php/', '', $_SERVER['PHP_SELF']) : '';
$uri = isset($_SERVER['REQUEST_URI']) ? explode('?', $_SERVER['REQUEST_URI'])[0] : '';
if ($self !== $uri) {
  $peaces = explode('/', $self);
  array_pop($peaces);
  $start = implode('/', $peaces);
  $search = '/' . preg_quote($start, '/') . '/';
  $uri = preg_replace($search, '', $uri, 1);
}

//pega o método da requisição
$action = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

$router->start($action, $uri);//roda o roteador

?>