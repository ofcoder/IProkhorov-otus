<?php

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


class emptyClass
{
  // This class is empty
};

try {
  emptyClass::one();
} catch (Throwable  $e) {
  // $e->getMessage() contains the error message
  Debug::dumpToFile($e, 'Throwable', '/local/Logs/debug_db-'.date("Y-m-d").'.txt');
  $loggerDb = new \Ofcoder\Diag\OfcoderDBExceptionHandlerLog;

  $loggerDb->write($e, 0);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");

