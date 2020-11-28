<?php

namespace Src\Model;

use Src\Error;

require_once(DIR_DB_CONNECTION);

class MakeModel {
  public static function Retrieve ($col, $val) {//retorna um array associativo com base numa busca simples sql
    $str = "select * from tb_fabricante where # = ?";
    switch ($col) {//0: id, 1: nome
      case 0:
        $attr = "cd_usuario";
        $type = "i";
      break;
      case 1:
        $attr = "nm_usuario";
        $type = "s";
      break;
    }
    if (isset($attr)) {//se attr existe, substitui o # pelo atributo e executa a query
      $str = str_replace("#", $attr, $str);
      $query = $DB->prepare($str);
      $query->bind_param($type, $val);
      try {//tenta executar o select
        $query->execute();
      } catch (mysqli_sql_exception $e) {
        throw new SysException();
      }
      $res = ($query->get_result())->fetch_assoc();//armazena a primeira linha
      $query->close();
      return $res;
    } else {//caso o parêmetro esteja errado, joga um erro
      throw new InvalidActionException("Coluna inexistente");
    }
  }
}

?>