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
      try {//se sim, retorna o id
        return $possibleUser["cd_usuario"];
      } catch (Exception $e) {//se não é possível, retorna false
        return false;
      }
    } else {//se a senha está incorreta, retorna false
      return false;
    }
  }

  public static function checkOwnership ($carId, $userId) {
    require_once(DIR_DB_CONNECTION);
    $err = !$DB->begin_transaction();
    $query = $DB->prepare("select * from tb_carro where cd_carro = ? and cd_usuario = ?");
    if ($query->bind_param("ii", $carId, $userId)) {//atrela os parâmetros
      try {
        $query->execute();
      } catch (\mysqli_sql_exception $e) {
        $DB->rollback();
        throw new Error\SysException("Erro no banco de dados");
      }
      $res = ($query->get_result())->fetch_all(MYSQLI_ASSOC);//armazena todas as linhas
    } else {//se os parametros estiverem errados
      $DB->rollback();
      throw new Error\InvalidActionException("Dados inválidos");
    }
    $query->close();
    if ($err || !$DB->commit()) {
      $DB->rollback();
      throw new Error\SysException("Erro no banco de dados");
    }
    return (\count($res) > 0);//pela organização do banco de dados, só é possível haver uma linha com esses mesmos valores, logo, basta checar se veio algum valor ou não
  }

  public static function listCars ($userId, $page) {
    require_once(DIR_DB_CONNECTION);
    $query = $DB->prepare("
      select cd_carro as id, (
        select nm_fabricante 
        from tb_fabricante 
        where tb_fabricante.cd_fabricante = tb_carro.cd_fabricante
      ) as makeName, nm_modelo_carro as model, nm_cor_carro as color, aa_carro as year, cd_placa_carro as numberPlate
      from tb_carro
      where cd_usuario = ?
      order by makeName
      limit 15
      offset ?
    ");
    $page = ($page-1)*15;
    if ($query->bind_param("ii", $userId, $page)) {
      try {
        $query->execute();
      } catch (\mysqli_sql_exception $e) {
        throw new Error\SysException("Erro no banco de dados");
      }
      $res = ($query->get_result())->fetch_all(MYSQLI_ASSOC);
      $query->close();
      return $res;
    } else {
      throw new Error\InvalidActionException("Dados inválidos");
    }
  }
}

?>