<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$dateTimeNow = date_create();
$dateString = date_format($dateTimeNow, 'Y-m-d H:i:s');
$date = date('Y-m-d H:i:s');
$dirLog = $_SERVER["DOCUMENT_ROOT"] . "/local/logs";
$fileLog = "timeLog.txt";
if (!is_dir($dirLog)) {
  mkdir($dirLog, 0777, true);
}
file_put_contents($dirLog . "/" . $fileLog, $dateString . PHP_EOL, FILE_APPEND);

\Ofcoder\Diagnostic\Helper::myDump($dateString);
\Ofcoder\Diagnostic\Helper::log2file($dateString);
\Ofcoder\Diagnostic\Helper::writeToLog($dateString, 'Date: ');
\Ofcoder\Diagnostic\Helper::bitrixDumpToFile($dateString, 'Date: ');

