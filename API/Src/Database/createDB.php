<?php
$dump = \file_get_contents(__DIR__."\dump\structure.sql");//pega o dump e separa em vários comandos
$host = $_ENV['DB_HOST'];//pega as variaveis de ambiente
$port = $_ENV['DB_PORT'];      
$user = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
try {
  $connection = new mysqli($host, $user, $password, null, $port);
} catch (\Exception $e) {
  //bloco vazio, pois o erro irá acontecer (o arquivo só é executado quando não há base de dados)
}
$connection->set_charset("utf8mb4");//configura o tipo de caracteres
try {
  $connection->begin_transaction();//tenta executar o dump por meio de uma transação (uma ação que pode ser revertida)
  $connection->multi_query($dump);
  sleep(1);//existe um problema aqui:
  /*
  O PHP trabalha de maneira síncrona na execução do código, só que o banco de dados não.
  Por isso eu preciso atrasar a execução do resto do script, para dar tempo da tabela ser criada, deixei assim pois esse script só será (na teoria) executado uma vez, que seria na primeira requisição que o servidor receber
  */
  $connection->commit();        
} catch (Throwable $e) {//denovo, se der erro ele para tudo (e volta ao estado anterior do banco)
  $connection->rollback();
}
$connection->close();
?>