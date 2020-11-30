<?php

use Src\Lib\Router\Router;
use Src\Lib\Router\Route;

$routes = [];
//TODO: inserir todos os controllers aqui
$user = new Route("/user");
$user->addCallback("POST", ["Src\Controller\UserController", "createUser"]);//AÇÃO
$user->addMiddleware("DELETE", ["Src\Controller\UserController", "auth"]);//AUTORIZAÇÃO
$user->addCallback("DELETE", ["Src\Controller\UserController", "deleteUser"]);//AÇÃO
array_push($routes, $user);

$auth = new Route("/auth");
$auth->addCallback("POST", ["Src\Controller\UserController", "login"]);//AÇÃO
array_push($routes, $auth);

$car = new Route("/car");
$car->addMiddleware("POST", ["Src\Controller\UserController", "auth"]);//AUTORIZAÇÕES
$car->addMiddleware("GET", ["Src\Controller\UserController", "auth"]);
$car->addMiddleware("DELETE", ["Src\Controller\UserController", "auth"]);
$car->addCallback("POST", ["Src\Controller\CarController", "addCar"]);//AÇÕES
$car->addCallback("GET", ["Src\Controller\CarController", "listCars"]);
$car->addCallback("DELETE", ["Src\Controller\CarController", "removeCar"]);
array_push($routes, $car);

$make = new Route("/make");
$make->addCallback("GET", ["Src\Controller\MakeController", "getMakes"]);//AÇÃO
array_push($routes, $make);

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