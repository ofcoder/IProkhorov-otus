<?php
echo "start debug_monolog<br>";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$logFile = $_SERVER["DOCUMENT_ROOT"] . "/local/logs/monolog_debug.log";

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger("daily");

$stream_handler = new StreamHandler("$logFile", Level::Error);

$logger->pushHandler($stream_handler);

$logger->debug("This is a debug message.");
$logger->info("This is an info level message.");
$logger->notice("This is a notice level message.");
$logger->warning("This is a warning level message.");
$logger->error("This is an error level message.");
$logger->critical("This is a critical level message.");
$logger->alert("This is an alert level message.");
$logger->emergency("This is an emergency level message.");



//Форматирование

use Monolog\Formatter\LineFormatter;

$logger = new Logger("my_logger");

//В этом примере,
// ProcessIdProcessor чтобы добавить идентификатор процесса скрипта,
// GitProcessor добавляет текущую ветку git и хэш коммита,
// MemoryUsageProcessor записывает использование памяти приложением.
//WebProcessor можно добавить текущую URI запроса, заявку способ и IP-адрес клиента в журнал записи
// HostnameProcessor может быть использовано чтобы добавить текущее имя хоста

$logger->pushProcessor(new \Monolog\Processor\ProcessIdProcessor());
$logger->pushProcessor(new \Monolog\Processor\GitProcessor());
$logger->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor());
$logger->pushProcessor(new \Monolog\Processor\WebProcessor());
$logger->pushProcessor(new \Monolog\Processor\HostnameProcessor());

$stream_handler = new StreamHandler("$logFile", Level::Debug);
$output = "%level_name% | %datetime% > %message% | %context% %extra%\n";
$dateFormat = "Y-n-j, g:i a";
$formatter = new LineFormatter(
  $output, // Format of message in log
  $dateFormat, // Datetime format
  true, // allowInlineLineBreaks option, default false
  true  // discard empty Square brackets in the end, default false
);
$stream_handler->setFormatter($formatter);

$logger->pushHandler($stream_handler);

$logger->debug("This file has been executed");

//Свой класс
$db_handler = new MonologDBHandler(new PDO('sqlite:debug.sqlite'));
$logger->pushHandler($db_handler);
$logger->debug("This file has been executed.");

echo "end debug_monolog<br>";


