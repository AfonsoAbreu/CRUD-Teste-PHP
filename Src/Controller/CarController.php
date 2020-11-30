<?php

namespace Src\Controller;
use Src\Lib\Router\Request;
use Src\Lib\Router\Response;
use Src\Model\CarModel;
use Src\Model\UserModel;
use Src\Model\Misc;
use Src\Error;
use Src\Error\Extensions;

class CarController {
  public static function addCar (Request $request, Response $response) {
    $makeId = $request->body["makeId"];
    $userId = $request->body["token"];
    $carModel = $request->body["model"];
    $carColor = $request->body["color"];
    $carYear = $request->body["year"];
    $carNumberPlate = $request->body["numberPlate"];//pega os campos
    try {
      CarModel::Create($makeId, $userId, $carModel, $carColor, $carYear, $carNumberPlate);
    } catch (Extensions\VehicleExistsException $e) {
      $response->abortWithError(400, "Vehicle number plate already exists");
      return false;//não executará as próximas funções da pilha
    } catch (Error\InvalidActionException $e) {
      $response->abortWithError(400, "Invalid POST data");
      return false;//não executará as próximas funções da pilha
    } catch (Error\SysException $e) {
      $response->abortWithError(500, "An unexpected error occurred");
      return false;//não executará as próximas funções da pilha
    } 

    $response->setStatus(200);//caso não hajam erros, retorna uma response vazia com o status 200
    $response->send();
  }

  public static function removeCar (Request $request, Response $response) {
    $userId = $request->body["token"];
    $carId = $request->body["carId"];
    try {//checa se o carro é realmente do usuário
      $isOwner = Misc::checkOwnership($carId, $userId);
    } catch (Error\SysException $e) {
      $response->abortWithError(500, "An unexpected error occurred");
      return false;//não executará as próximas funções da pilha
    }
    if ($isOwner) {
      try {
        CarModel::Delete($carId);
      } catch (Error\SysException $e) {
        $response->abortWithError(500, "An unexpected error occurred");
        return false;
      } catch (Extensions\NotFoundException $e) {
        $response->abortWithError(400, "No such car found");
        return false;
      }
    } else {
      $response->abortWithError(403, "User doesen't own said car");
      return false;
    }

    $response->setStatus(200);
    $response->send();
  }

  public static function listCars (Request $request, Response $response) {
    $page = $request->params["page"];
    $userId = $request->body["token"];

    try {
      $res = Misc::listCars($userId, $page);
    } catch (Error\SysException $e) {
      $response->abortWithError(500, "An unexpected error ocurred");
      return false;
    } catch (Error\InvalidActionException $e) {
      $response->abortWithError(400, "Invalid input value");
      return false;
    }

    $response->setBody($res);
    $response->setStatus(200);
    $response->send();
  } 
}

?>