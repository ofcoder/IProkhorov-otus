<?php
namespace Ofcoder\Diagnostic;

class Helper
{
    //const FILE_NAME = DEBUG_FILE_NAME;
  public static function writeToLog($data, $title = ''): bool
  {
    if (!DEBUG_FILE_NAME)
      return false;
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(DEBUG_FILE_NAME, $log, FILE_APPEND);
    return true;
  }

  public static function myDump($var)
  {
    global $USER;
    if( ($USER->isAdmin() == 1) || ($REQUEST["dump"] === "Y"))
    {
      ?>
      <font style="text-align: left; font-size: 10px"><pre><?var_dump($var)?></pre></font><br>
      <?
    }

  }
  public static function bitrixDumpToFile($var, $varName = '')
  {
    /**
     * @param mixed $variable Логируемая переменная
     * @param string $varName Название переменной в лог-файле
     * @param string $fileName Имя файла для сохранения лога
     */
    $variable = $var;
    $fileName = DEBUG_FILE_NAME;
    $varName = $varName;
    try {
      \Bitrix\Main\Diag\Debug::dumpToFile($variable, $varName, $fileName);
    }catch (\Exception $e ){
      \Bitrix\Main\Diag\Debug::dumpToFile($e, 'Ошибка bitrixDumpToFile', $fileName);
    }

  }
}