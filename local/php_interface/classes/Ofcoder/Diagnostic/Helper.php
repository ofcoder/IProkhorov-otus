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
  public static function log2file($var, $fn = null, $folder="/local/logs/")
  {
    $folder = $_SERVER["DOCUMENT_ROOT"] . $folder;
    if(!file_exists($folder))
    {
      mkdir($folder, 0777, true);
    }
    $error = "";
    $fn = $fn ? "-" . str_replace(['\\', '/', ' '], '', $fn) : "";
    $fp = fopen($folder . date("Y") . "-log2file{$fn}.txt", "a");
    $typeVar = gettype($var);
    if($typeVar == "string" || $typeVar == "integer" || $typeVar == "double" || $typeVar !== "boolean"){
      $test = fwrite($fp, date("Y-m-d H:i:s") . ":" . print $var. "\r\n");
    }else{
      $test = fwrite($fp, date("Y-m-d H:i:s") . ";" . var_dump($var) . "\r\n");
    }
    if (!$test) {
      $error = "Ошибка при записи в файл " . $folder . date("Y") . "-log2file{$fn}.txt";

    }
    fclose($fp);

    return $error;
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