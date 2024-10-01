<?php
//источник https://betterstack.com/community/guides/logging/how-to-start-logging-with-monolog/#creating-custom-handlers-in-monolog
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


use Monolog\Level;
use Monolog\Logger;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

// New Logger instance
$logger = new Logger("my_logger");
$formatter = new JsonFormatter();

// Create new handler
$rotating_handler = new RotatingFileHandler(__DIR__ . "/log/debug.log", 30, Level::Debug);
$stream_handler = new StreamHandler(__DIR__ . "/log/notice.log", Level::Notice);
$db_handler = new MonologDBHandler(new PDO('sqlite:alert.sqlite'), Level::Alert);

$stream_handler->setFormatter($formatter);
$db_handler->setFormatter($formatter);
$rotating_handler->setFormatter($formatter);

// Push the handler to the log channel
$logger->pushHandler($stream_handler);
$logger->pushHandler($rotating_handler);
$logger->pushHandler($db_handler);

// Log the message
$logger->info("This file has been executed.");
$logger->error("An error occurred.");
$logger->critical("This application is in critical condition!!");
$logger->emergency("This is an EMERGENCY!!!");

?>
