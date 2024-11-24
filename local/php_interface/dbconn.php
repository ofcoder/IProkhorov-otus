<?php
//https://dev.1c-bitrix.ru/api_help/main/general/magic_vars.php


define("BX_FILE_PERMISSIONS", 0644);
define("BX_DIR_PERMISSIONS", 0755);
@umask(~(BX_FILE_PERMISSIONS | BX_DIR_PERMISSIONS) & 0777);

@ini_set("memory_limit", "1024M");

define("BX_DISABLE_INDEX_PAGE", true);

mb_internal_encoding("UTF-8");

if (!defined('DEBUG_FILE_NAME')){
  define('DEBUG_FILE_NAME', $_SERVER['DOCUMENT_ROOT'] . '/local/Logs/SQL-'.date("Y-m-d").'.txt');
}
if (!defined('BX_SQL_LOG ') && defined('DEBUG_FILE_NAME')){
  define('BX_SQL_LOG', true);
  // $DBDebug = false; // вывод sql запросов на экран
  $DBDebugToFile = false; // запись sql запросов в файл /mysql_debug.sql
}
