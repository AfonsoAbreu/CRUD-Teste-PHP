<?php

namespace Src\Database;
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
    $this->connection = new \mysqli($host, $user, $password, $dbname, $port);//conecta com o banco
    $this->connection->set_charset("utf8mb4");//configura o tipo de caracteres
    if ($this->connection->connect_errno) {//caso alguma merda ocorra, finaliza o script
      exit("Conexão mal-sucedida");
    }
  }

  public function getConnection () {//esse getter só existe pois não pode haver um setter, e isso não é C#
    return $this->connection;
  }
}

?>