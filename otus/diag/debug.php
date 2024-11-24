<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$dateTimeNow = date_create();
$dateString = date_format($dateTimeNow, 'Y-m-d H:i:s');
$date = date('Y-m-d H:i:s');
$dirLog = $_SERVER["DOCUMENT_ROOT"] . '/local/Logs/';
$fileLog = "timeLog.txt";
if (!is_dir($dirLog)) {
  mkdir($dirLog, 0777, true);
}
file_put_contents($dirLog . "/" . $fileLog, $dateString . PHP_EOL, FILE_APPEND);
/*
\Ofcoder\Diag\Helper::myDump($dateString);
\Ofcoder\Diag\Helper::log2file($_REQUEST);
\Ofcoder\Diag\Helper::writeToLog($dateString, 'Date: ');
\Ofcoder\Diag\Helper::bitrixDumpToFile($dateString, 'Date: ');
*/

