<?php

namespace Src\Model;

use Src\Error;
use Src\Error\Extensions;

class UserModel {
  private static $regexName = "/^\b([A-ZÀ-ÿ][-,a-z. ']+[ ]*)+$/u";
  private static $regexPassword = "/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{8,32}$/";
  
  public static function Create ($name, $email, $pass) {
    require_once(DIR_DB_CONNECTION);
    if (
      preg_match(self::$regexName, $name) === 1 && 
      filter_var($email, FILTER_VALIDATE_EMAIL) && 
      preg_match(self::$regexPassword, $pass)
    ) {//valida se os dados estão dentro dos conformes
      $err = !$DB->begin_transaction();//começa a transação, como essa função e a commit() podem retornar false, ambas estão dentro de um if (para lançar uma exceção posteriormente)
      $query = $DB->prepare("insert into tb_usuario (nm_usuario, cd_email_usuario, cd_senha_usuario) values ( ? , ? , ? )");
      // var_dump($DB->query("show tables")->fetch_all(MYSQLI_ASSOC));
      $pass = password_hash($pass, PASSWORD_BCRYPT);//criptografa a senha
      if ($query->bind_param("sss", $name, $email, $pass)) {//se os parâmetros estão corretos
        try {//tenta inserir
          $query->execute();
          $query->close();
        } catch (\mysqli_sql_exception $e) {//caso haja um erro, joga um erro de sistema
          $DB->rollback();//retorna o banco ao estado anterior
          $realerror = new Error\DBException($e);
          if ($realerror->code === 1062) {//código de erro mysql para falha numa restrição UNIQUE
            throw new Extensions\EmailExistsException();
          } else {
            throw new Error\SysException("Erro no banco de dados");
          }
        }
      } else {//senão, joga um erro
        $DB->rollback();
        throw new Error\InvalidActionException("Dados Inválidos");
      }
      if ($err || !$DB->commit()) {
        $DB->rollback();
        throw new Error\SysException("Erro no banco de dados");
      }
    } else {//caso os dados não estejam no conforme, joga um erro
      throw new Error\InvalidActionException("Dados Inválidos");
    }
  }

  public static function Retrieve ($col, $val) {//retorna um array associativo com base numa busca simples sql
    require(DIR_DB_CONNECTION);
    $str = "select * from tb_usuario where # = ?";
    switch ($col) {//0: id, 1: nome, 2: email, 3: senha
      case 0:
        $attr = "cd_usuario";
        $type = "i";
      break;
      case 1:
        $attr = "nm_usuario";
        $type = "s";
      break;
      case 2:
        $attr = "cd_email_usuario";
        $type = "s";
      break;
      case 3:
        $attr = "cd_senha_usuario";
        $type = "s";
      break;
    }
    if (isset($attr)) {//se attr existe, substitui o # pelo atributo e executa a query
      $str = str_replace("#", $attr, $str);
      $query = $DB->prepare($str);
      if ($query->bind_param($type, $val)) {//se os parametros são válidos
        try {//tenta executar o select
          $query->execute();
        } catch (\mysqli_sql_exception $e) {
          throw new Error\SysException();
        }
        $res = ($query->get_result())->fetch_all(MYSQLI_ASSOC);//armazena todas as linhas
        $query->close();
        return $res;
      } else {//se eles não são, joga um erro
        throw new Error\InvalidActionException("Dados Inválidos");
      }
    } else {//caso o parêmetro esteja errado, joga um erro
      throw new Error\InvalidActionException("Coluna inexistente");
    }
  }
  
  public static function Update ($id, $col, $val) {
    require_once(DIR_DB_CONNECTION);
    $str = "update tb_usuario set # = ? where cd_usuario = ?";
    switch ($col) {//0: nome, 1: email, 2: senha
      case 0:
        if (preg_match(self::$regexName, $val) === 1) {
          $attr = "nm_usuario";
        }
      break;
      case 1:
        if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
          $attr = "cd_email_usuario";
        }
      break;
      case 2:
        if (preg_match(self::$regexPassword, $val) === 1) {
          $attr = "cd_senha_usuario";
        }
      break;
    }
    if (isset($attr)) {//se attr existe, substitui o # pelo atributo e executa a query
      $str = str_replace("#", $attr, $str);
      $err = !$DB->begin_transaction();
      $query = $DB->prepare($str);
      if ($query->bind_param("si", $val, $id)) {//se os parâmetros forem válidos
        try {
          $query->execute();//executa a query
        } catch (\mysqli_sql_exception $e) {
          $DB->rollback();
          $realerror = new Error\DBException($e);
          if ($realerror->code === 1062) {//se esse email já estiver cadastrado
            throw new Extensions\EmailExistsException();
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
        throw new Error\InvalidActionException("Dados Inválidos");
      }
      $query->close();
      if ($err || !$DB->commit()) {
        $DB->rollback();
        throw new Error\SysException("Erro no banco de dados");
      }
    } else {//se attr não existe, significa que o parametro col foi preenchido de maneira errada
      throw new Error\InvalidActionException("Coluna inexistente");
    }
  }
  
  public static function Delete ($id) {
    require_once(DIR_DB_CONNECTION);
    $err = !$DB->begin_transaction();
    $query = $DB->prepare("delete from tb_usuario where cd_usuario = ?");
    if ($query->bind_param("i", $id)) {//prepara a query, se ps parametros forem válidos
      try {
        $query->execute();//deleta
      } catch (\mysqli_sql_exception $e) {//caso dê erro, sai do processo
        $DB->rollback();
        throw new Error\SysException("Erro no banco de dados");
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