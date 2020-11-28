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
        $response->setStatus(400);
        $error = ["Email already exists"];
        $response->setBody($error);
        $response->send();
        return false;//não executará as próximas funções da pilha
      } catch (Error\SysException $e) {
        $response->setStatus(500);
        $error = ["An unexpected error occurred"];
        $response->setBody($error);
        $response->send();
        return false;//não executará as próximas funções da pilha
      } catch (Error\InvalidActionException $e) {
        $response->setStatus(400);
        $error = ["Invalid POST data"];
        $response->setBody($error);
        $response->send();
        return false;//não executará as próximas funções da pilha
      }
      
      $response->setStatus(200);//caso não hajam erros, retorna uma response vazia com o status 200
      $response->send();
    }

    public static function login (Request $request, Response $response) {
      $password = $request->body["password"];
      $email = $request->body["email"];
      if ($uid = Misc::tryLogin($email, $password)) {//tenta fazer login, se estiver tudo certo, configura o status code para OK e atribui uid ao id do usuário
        $response->setStatus(200);
      } else {//se as credenciais estiverem erradas, configura o status code para Forbidden, adiciona um erro e manda a response
        $response->setStatus(403);
        $error = ["Invalid login credentials"];
        $response->setBody($error);
        $response->send();
        return false;
      }

      $secret = $_ENV["JWT_SECRET"];//pega o segredo (um monte de caracteres aleatórios) das variáveis de ambiente
      $payload = [
        'iat' => time(),
        'iss' => $_SERVER['SERVER_NAME'],
        'exp' => time() + 600,
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
          $jwt = JWT::decode($jwtEncripted, $secret, ["HS512"]);
        } catch (SignatureInvalidException $e) {//exception da lib, para caso os dados não batam
          $response->setStatus(403);
          $error = ["Invalid Authentication Method"];
          $response->setBody($error);
          $response->send();
          return false;
        } catch (ExpiredException $e) {//exception da lib, para caso o token tenha expirado
          $response->setStatus(403);
          $error = ["Expired Token"];
          $response->setBody($error);
          $response->send();
          return false;
        }
        $request->body["token"] = (array) $jwt["data"]["id"];//converte o token para somente o email, o resto não importa
      } else {
        $response->setStatus(400);//caso não haja um token, retorna 400 Bad Request
        $error = ["Missing Token"];
        $response->setBody($error);
        $response->send();
        return false;
      }
    }
  }

?>