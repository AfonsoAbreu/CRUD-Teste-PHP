<?php

namespace Src\Model;

use Src\Error;


class MakeModel {
  public static function Retrieve () {//retorna um array associativo com todas as marcas (esse é um caso especial)
    require_once(DIR_DB_CONNECTION);
    $str = "select cd_fabricante as id, nm_fabricante as name from tb_fabricante";
    $query = $DB->prepare($str);
    try {//tenta executar o select
      $query->execute();
    } catch (mysqli_sql_exception $e) {
      throw new SysException();
    }
    $res = $query->get_result();
    $content = [];
    for ($i = 0; $i < $res->num_rows; $i++) {
      $content[$i] = $res->fetch_assoc();
    }
    $query->close();
    return $content;
  }
}

?>