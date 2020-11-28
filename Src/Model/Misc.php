<?php

namespace Src\Model;

use Src\Model;
use Src\Model\UserModel;
use Src\Error;

class Misc {
  public static function tryLogin ($email, $pass) {
    $possibleUser = UserModel::Retrieve(2, $email);//possível usuário
    if (count($possibleUser) !== 1) {//se não houveram usuários, retorna false
      return false;
    }
    $possibleUser = $possibleUser[0];//pega o primeiro resultado (e por causa das restrições que botei no banco, o único)
    if (password_verify($pass, $possibleUser["cd_senha_usuario"])) {//confere se a senha está certa
      try {//tenta pegar o id do usuário com base em seu email
        return UserModel::Retrieve(2, $email)["cd_usuario"];
      } catch (Exception $e) {//se não é possível, retorna false
        return false;
      }
    } else {//se a senha está incorreta, retorna false
      return false;
    }
  }

  public static function checkOwnership ($carId, $userId) {
    require_once(DIR_DB_CONNECTION);
    $query = $DB->prepare("select * from tb_carro where cd_carro = ? and cd_usuario = ?");
    $query->bind_param("ii", $carId, $userId);
    try {
      $query->execute();
    } catch (\mysqli_sql_exception $e) {
      throw new Error\SysException("Erro no banco de dados");
    }
    $res = ($query->get_result())->fetch_all(MYSQLI_ASSOC);//armazena todas as linhas
    $query->close();
    return (\count($res) > 0);//pela organização do banco de dados, só é possível haver uma linha com esses mesmos valores, logo, basta checar se veio algum valor ou não
  }
}

?>