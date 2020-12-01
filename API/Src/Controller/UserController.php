<?php

namespace Src\Controller;
use Src\Lib\Router\Request;
use Src\Lib\Router\Response;
use Src\Model\UserModel;
use Src\Model\Misc;
use Src\Error;
use Src\Error\Extensions;
use Firebase\JWT;

class UserController {
  public static function createUser (Request $request, Response $response) {
    $username = $request->body["username"];
    $email = $request->body["email"];
    $password = $request->body["password"];

    try {
      UserModel::Create($username, $email, $password);
    } catch (Error\Extensions\EmailExistsException $e) {
      $response->abortWithError(400, "Email already exists");
      return false;//não executará as próximas funções da pilha
    } catch (Error\SysException $e) {
      $response->abortWithError(500, "An unexpected error occurred");
      return false;//não executará as próximas funções da pilha
    } catch (Error\InvalidActionException $e) {
      $response->abortWithError(400, "Invalid POST data");
      return false;//não executará as próximas funções da pilha
    }
    
    $response->setStatus(200);//caso não hajam erros, retorna uma response vazia com o status 200
    $response->send();
  }

  public static function login (Request $request, Response $response) {
    $password = $request->body["password"];
    $email = $request->body["email"];
    try {
      $uid = Misc::tryLogin($email, $password);
    } catch (Error\SysException $e) {
      $response->abortWithError(500, "An unexpected error occurred");
      return false;
    }
    if ($uid) {//tenta fazer login, se estiver tudo certo, configura o status code para OK e atribui uid ao id do usuário
      $response->setStatus(200);
    } else {//se as credenciais estiverem erradas, configura o status code para Forbidden, adiciona um erro e manda a response
      $response->abortWithError(403, "Invalid login credentials");
      return false;
    }

    $secret = $_ENV["JWT_SECRET"];//pega o segredo (um monte de caracteres aleatórios) das variáveis de ambiente
    $payload = [
      'iat' => time(),
      'iss' => $_SERVER['SERVER_NAME'],
      'exp' => time() + 3600,
      'data' => [
        'id' => $uid
      ]
    ];//carga que será encriptada no JSON Web Token
    $jwt = JWT\JWT::encode($payload, $secret, "HS512");//usa o método encode da lib, que vai retornar uma string json contendo o webtoken
    $response->setBodyRaw($jwt);//vai responder somente com status 200 OK e o JWT no body
    $response->send();
  }

  public static function auth (Request $request, Response $response) {
    if (isset($request->body["token"])) {
      $jwtEncripted = $request->body["token"];
      $secret = $_ENV["JWT_SECRET"];
      try {//tenta decodificar o JWT
        $jwt = JWT\JWT::decode($jwtEncripted, $secret, ["HS512"]);
      } catch (JWT\SignatureInvalidException $e) {//exception da lib, para caso os dados não batam
        $response->abortWithError(403, "Invalid authentication method");
        return false;
      } catch (JWT\ExpiredException $e) {//exception da lib, para caso o token tenha expirado
        $response->abortWithError(403, "Expired token");
        return false;
      } catch (\Exception $e) {//exception genérica
        $response->abortWithError(403, "Invalid token");
        return false;
      }
      $request->body["token"] = $jwt->data->id;//converte o token para somente o email, o resto não importa
    } else {
      $response->abortWithError(400, "Missing token");
      return false;
    }
  }

  public static function deleteUser (Request $request, Response $response) {
    $userId = $request->body["token"];
    try {
      UserModel::Delete($userId);
    } catch (Error\SysException $e) {
      $response->abortWithError(500, "An unexpected error occurred");
    }
  }

  public static function getOwnInfo (Request $request, Response $response) {
    $userId = $request->body["token"];
    try {
      $result = UserModel::Retrieve(0, $userId)[0];
    } catch (Error\SysException $e) {
      $response->abortWithError(500, "An unexpected error ocurred");
    }
    $result = ["name" => $result["nm_usuario"], "email" => $result["cd_email_usuario"]];
    $response->setBody($result);
    $response->send();
  }
}

?>