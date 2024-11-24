<?php
namespace Ofcoder\Diag;

use Bitrix\Main\Diag\FileExceptionHandlerLog;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;

class OfcoderFileExceptionHandlerLog extends FileExceptionHandlerLog
{
  private $level = 0;
  public function write($exception, $logType)
  {
    /**
     * bitrix/modules/main/lib/diag/fileexceptionhandlerlog.php
     * fileexceptionhandlerlog->write($exception, $logType)
     *
     * $text = ExceptionHandlerFormatter::format($exception, false, $this->level);
     *
     * $context = [
     * 'type' => static::logTypeToString($logType),
     * ];
     *
     * $logLevel = static::logTypeToLevel($logType);
     *
     * $message = "{date} - Host: {host} - {type} - {$text}\n";
     *
     * $this->logger->log($logLevel, $message, $context);
 *
* /************************************/

    $text = ExceptionHandlerFormatter::format($exception);

    $context = [
      'type' => static::logTypeToString($logType),
    ];

    $logLevel = static::logTypeToLevel($logType);
    $message = "OTUS-{date} - Host: {host} - {type} - {$text}\n";
    $lines = explode("\n", $message);

    foreach ($lines as &$line) {
      $line = 'OTUS - ' . $line;
    }

    $message = implode("\n", $lines);
    $this->logger->log($logLevel, $message, $context);
     /* */
  }
}