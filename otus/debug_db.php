<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$loggerDb = new \Ofcoder\Diag\OfcoderDBExeptionHandlerLog;
$loggerDb->write('test', 'ERROR');
class emptyClass
{
  // This class is empty
};

// Try to access a property that does not exist
emptyClass::one();

