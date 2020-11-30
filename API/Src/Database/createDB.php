<?php
// meus parabéns se você conseguir fazer esse arquivo rodar como um comando pelo o composer, eu estou tentando fazer isso faz uma hora, e ainda não consegui
require_once '../../../vendor/autoload.php';//load automático das classes
$dotenv = Dotenv\Dotenv::createImmutable("../../../");// configura o Dotenv (sistema de variáveis globais)
$dotenv->load();//carrega o Dotenv

$dump = \file_get_contents(__DIR__."\dump\structure.sql");//pega o dump
$host = $_ENV['DB_HOST'];//pega as variaveis de ambiente
$port = $_ENV['DB_PORT'];      
$dbname = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
\mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);//manda o mysql lançar exceptions caso necessário
$connection = new mysqli($host, $user, $password, $dbname, $port);
$connection->set_charset("utf8mb4");//configura o tipo de caracteres
if ($connection->connect_errno) {//caso alguma merda ocorra, finaliza o script
  exit("Conexão mal-sucedida");
}
try {
  $connection->begin_transaction();//tenta executar o dump por meio de uma transação (uma ação que pode ser revertida)
  $connection->multi_query($dump);
  $connection->commit();        
} catch (Throwable $e) {//denovo, se der erro ele para tudo (e volta ao estado anterior do banco)
  $connection->rollback();
  exit($e->getMessage());
}

exit(0);

?>