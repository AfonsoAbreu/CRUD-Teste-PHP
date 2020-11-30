<?php

require_once 'vendor/autoload.php';//load automático das classes
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);// configura o Dotenv (sistema de variáveis globais)
$dotenv->load();//carrega o Dotenv

require_once 'dirconfig.php';
require_once 'Src/routes.php';//o arqivo de rotas do sistema

?>