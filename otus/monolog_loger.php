<?php
//источник https://betterstack.com/community/guides/logging/how-to-start-logging-with-monolog/#creating-custom-handlers-in-monolog
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$logFile = $_SERVER["DOCUMENT_ROOT"] . "/local/logs/monolog_debug.log";

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

function exception_handler(Throwable $e)
{
  $logger = new Logger('uncaught');
  $stream_handler = new StreamHandler(__DIR__ . "/log/uncaught.log", Level::Debug);
  $stream_handler->setFormatter(new JsonFormatter());
  $logger->pushHandler($stream_handler);
  $logger->error("Uncaught exception", array('exception' => $e));
}

set_exception_handler("exception_handler");

class emptyClass
{
  // This class is empty
};

// Try to access a property that does not exist
emptyClass::one();
?>
