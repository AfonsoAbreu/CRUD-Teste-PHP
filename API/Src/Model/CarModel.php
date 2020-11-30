<?php

namespace Src\Model;

use Src\Error;
use Src\Error\Extensions;

class CarModel {
  public static function Create ($cd_fabricante, $cd_usuario, $nm_modelo_carro, $nm_cor_carro, $aa_carro, $cd_placa_carro) {
    require_once(DIR_DB_CONNECTION);
    $err = !$DB->begin_transaction();//começa a transação, como essa função e a commit() podem retornar false, ambas estão dentro de um if (para lançar uma exceção posteriormente)
    $query = $DB->prepare("insert into tb_carro (cd_fabricante, cd_usuario, nm_modelo_carro, nm_cor_carro, aa_carro, cd_placa_carro) values ( ? , ? , ? , ? , ? , ? )");
    if ($query->bind_param("iissss", $cd_fabricante, $cd_usuario, $nm_modelo_carro, $nm_cor_carro, $aa_carro, $cd_placa_carro)) {
      try {//tenta inserir
        $query->execute();
        $query->close();
      } catch (\mysqli_sql_exception $e) {//caso haja um erro, joga um erro de sistema
        $DB->rollback();
        $realerror = new Error\DBException($e);
        if ($realerror->code === 1062) {//código de erro mysql para falha numa restrição UNIQUE
          throw new Extensions\VehicleExistsException();
        } else if ($realerror->code === 1452) {
          throw new Error\InvalidActionException("Dados inválidos");
        } else {
          throw new Error\SysException("Erro no banco de dados");
        }
      }
    } else {//se os parâmetros forem inválidos, joga um erro
      $DB->rollback();
      throw new Error\InvalidActionException("Dados inválidos");
    }
    if ($err || !$DB->commit()) {
      $DB->rollback();
      throw new Error\SysException("Erro no banco de dados");
    }
  }

  public static function Retrieve ($col, $val) {//retorna um array associativo com base numa busca simples sql
    require_once(DIR_DB_CONNECTION);
    $str = "select * from tb_carro where # = ?";
    switch ($col) {//0: id, 1: id_fabricante, 2: id_usuario, 3: nome, 4: cor, 5: ano, 6: placa
      case 0:
        $attr = "cd_carro";
        $type = "i";
      break;
      case 1:
        $attr = "cd_fabricante";
        $type = "i";
      break;
      case 2:
        $attr = "cd_usuario";
        $type = "i";
      break;
      case 3:
        $attr = "nm_modelo_carro";
        $type = "s";
      break;
      case 4:
        $attr = "nm_cor_carro";
        $type = "s";
      break;
      case 5:
        $attr = "aa_carro";
        $type = "s";
      break;
      case 6:
        $attr = "cd_placa_carro";
        $type = "s";
      break;
    }
    if (isset($attr)) {//se attr existe, substitui o # pelo atributo e executa a query
      $str = str_replace("#", $attr, $str);
      $query = $DB->prepare($str);
      if ($query->bind_param($type, $val)) {
        try {//tenta executar o select
          $query->execute();
        } catch (\mysqli_sql_exception $e) {
          throw new Error\SysException();
        }
        $res = ($query->get_result())->fetch_all(MYSQLI_ASSOC);//armazena todas as linhas
      } else {
        throw new Error\InvalidActionException("Dados inválidos");
      }
      $query->close();
      return $res;
    } else {//caso o parêmetro esteja errado, joga um erro
      throw new Error\InvalidActionException("Coluna inexistente");
    }
  }
  
  public static function Update ($id, $col, $val) {
    require_once(DIR_DB_CONNECTION);
    $str = "update tb_carro set # = ? where cd_carro = ?";
    switch ($col) {//0: id, 1: id_fabricante, 2: id_usuario, 3: nome, 4: cor, 5: ano, 6: placa
      case 0:
        $attr = "cd_carro";
      break;
      case 1:
        $attr = "cd_fabricante";
      break;
      case 2:
        $attr = "cd_usuario";
      break;
      case 3:
        $attr = "nm_modelo_carro";
      break;
      case 4:
        $attr = "nm_cor_carro";
      break;
      case 5:
        $attr = "aa_carro";
      break;
      case 6:
        $attr = "cd_placa_carro";
      break;
    }
    if (isset($attr)) {//se attr existe, substitui o # pelo atributo e executa a query
      $str = str_replace("#", $attr, $str);
      $err = !$DB->begin_transaction();
      $query = $DB->prepare($str);
      if ($query->bind_param("si", $val, $id)) {
        try {
          $query->execute();//executa a query
        } catch (\mysqli_sql_exception $e) {
          $DB->rollback();
          if ($e->code = 1062) {//se esse email já estiver cadastrado
            throw new Extensions\VehicleExistsException();
          } else {
            throw new Error\SysException("Erro no banco de dados");
          }
        }
        if ($query->affected_rows !== 1) {//se a query não afetou uma linha, dá erro
          $DB->rollback();
          throw new Error\InvalidActionException("Nenhuma linha foi atualizada");
        }
      } else {
        $DB->rollback();
        throw new Error\InvalidActionException("Dados inválidos");
      }
      $query->close();
      if ($err || !$DB->commit()) {
        $DB->rollback();
        throw new Error\SysException("Erro no banco de dados");
      }
    } else {//se attr não existe, significa que o parametro col foi preenchido de maneira errada
      throw new Error\InvalidActionException ("Coluna inexistente");
    }
  }
  
  public static function Delete ($id) {
    require(DIR_DB_CONNECTION);
    $err = !$DB->begin_transaction();
    $query = $DB->prepare("delete from tb_carro where cd_carro = ?");
    if ($query->bind_param("i", $id)) {//prepara a query
      try {
        $query->execute();//deleta
      } catch (\mysqli_sql_exception $e) {//caso dê erro, sai do processo
        throw new Error\SysException("Erro no banco de dados");
      }
      if ($query->affected_rows !== 1) {
        throw new Extensions\NotFoundException();//se afetou nenhuma linha, joga um erro
      }
    } else {
      $DB->rollback();
      throw new Error\InvalidActionException("Dados Inválidos");
    }
    $query->close();
    if ($err || !$DB->commit()) {
      $DB->rollback();
      throw new Error\SysException("Erro no banco de dados");
    }
  }  
}

?>