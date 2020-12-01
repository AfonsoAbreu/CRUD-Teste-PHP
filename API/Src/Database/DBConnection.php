<?php

namespace Src\Database;

use Src\Error;
class DBConnection {
  private $connection;

  public function __construct () {//constrói a conexão com o banco e joga ela num atributo privado
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $dbname = $_ENV['DB_DATABASE'];
    $user = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];
    //sinceramente, eu usaria o PDO, mas isso significaria ter que rodar o banco em outro server (o USBWebserver não carrega o PDO)
    \mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);//manda o mysql lançar exceptions caso necessário
    try {
      $this->connection = new \mysqli($host, $user, $password, $dbname, $port);//conecta com o banco
    } catch (\mysqli_sql_exception $e) {
      $e = new Error\DBException($e);
      if ($e->code === 1049) {//erro para caso a base não exista
        require("createDB.php");//cria a base
        $this->connection = new \mysqli($host, $user, $password, $dbname, $port);//tenta denovo
      }
    }
    // var_dump($this->connection->query("show tables")->fetch_all(MYSQLI_ASSOC));
    if ($this->connection->connect_errno) {//caso alguma merda ocorra, finaliza o script
      exit("Conexão mal-sucedida");
    }
    $this->connection->set_charset("utf8mb4");//configura o tipo de caracteres
    $this->connection->autocommit(false);//desativa o commit automático para todas as queries
  }

  public function getConnection () {//esse getter só existe pois não pode haver um setter, e isso não é C#
    return $this->connection;
  }
}

?>