<?php

namespace Src\Controller;
use Src\Lib\Router\Request;
use Src\Lib\Router\Response;
use Src\Model\CarModel;
use Src\Model\MakeModel;
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
    if (!$isOwner) {//TODO: complete this

    }
    try {
      CarModel::Delete();
    }
  }
}

?>