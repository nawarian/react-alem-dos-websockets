<?php

// $ php log.php meuArquivo.log 'Texto para o log'

require_once dirname(__FILE__).'/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logFile = $loggerName = $argv[1]; // meuArquivo.log
$textToLog = $argv[2]; // Texto para o log

$log = new Logger($loggerName);
$log->pushHandler(new StreamHandler($logFile), Logger::WARNING);

$log->addInfo($textToLog);