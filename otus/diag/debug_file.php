<?php

//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';


class emptyClass
{
  // This class is empty
};

try {
  emptyClass::one();
} catch (Throwable  $e) {
  echo $e->getMessage() . '<br>'; // contains the error message
  $loggerFile = new \Ofcoder\Diag\OfcoderFileExceptionHandlerLog;

  $loggerFile->write($e, 0);
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
