<?php

namespace Src\Lib\Router;

class Filters {
  public static function _filterAction (&$str) {
    $str = strtoupper($str);
  }
  
  public static function _filterURI (&$str) {
    $str = strtolower($str);
    if (substr($str, 0, 1) !== '/') {
      $str = '/' . $str;
    }
  }
}

?>