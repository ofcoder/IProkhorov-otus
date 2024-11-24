<?php
  define("OFCODER_LOGGER", "FILE"); //FILE, DB

  define("OFCODER_DB_TABLE_PREFIX", "");
$exceptionHandling = array (
  'value' => array (
    'debug' => false, // disables error output to screen
    // ошибки для вывода в лог
    'handled_errors_types' => E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING,
    'exception_errors_types' => E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_COMPILE_WARNING,
    'ignore_silence' => true,
    'assertion_throws_exception' => true,
    'assertion_error_type' => 256,
    'log' => array (
      'settings' => array (
        'file' => "local/logs/" . date("Y-m-d") . ".txt",
        'log_size' => 1000000, // ~ 1Mb per file
      ),
    ),
  ),
  'readonly' => true,
);
  if(OFCODER_LOGGER == 'FILE'){
    $exceptionHandling = array (
      'value' =>
        array (
          'debug' => false,
          'handled_errors_types' => 4437,
          'exception_errors_types' => 4437,
          'ignore_silence' => false,
          'assertion_throws_exception' => true,
          'assertion_error_type' => 256,
          'log' => [
            'class_name' => 'Ofcoder\\Diag\\OfcoderFileExceptionHandlerLog', // Название своего класса
// Система ищет или в папке bitrix, или в local, путь начинается после bitrix/ или local/
            'required_file' => 'php_interface/classes/Ofcoder/Diag/OfcoderFileExceptionHandlerLog.php',
// Если БД недоступна, то хотя бы запишет в файл
            'settings' => [
              'file' => 'local/Logs/OfcoderFileExceptionHandlerLog' . date("Y-m-d") . '.txt',
              'log_size' => 1000000,
// Битрикс по умолчанию генерирует уйму, просто кучу исторических ошибок. Чтобы не забить, игнориуем один из типов
              'dont_show' => ['\Bitrix\Main\Diag\ExceptionHandlerLog::LOW_PRIORITY_ERROR']
            ],
          ],
        ),
      'readonly' => false,
    );
  }

if(OFCODER_LOGGER == 'DB'){
  $exceptionHandling = array (
    'value' =>
      array (
        'debug' => false,
        'handled_errors_types' => E_ALL & ~E_NOTICE & ~E_DEPRECATED,
        'exception_errors_types' => E_ALL & ~E_NOTICE & ~E_DEPRECATED,
        'ignore_silence' => true,
        'assertion_throws_exception' => false,
        'assertion_error_type' => 256,
        'log' => [
          'class_name' => 'Ofcoder\\Diag\\OfcoderDBExceptionHandlerLog', // Название своего класса
// Система ищет или в папке bitrix, или в local, то есть система сама подставит перед apps/...
          'required_file' => 'php_interface/classes/Ofcoder/Diag/OfcoderDBExceptionHandlerLog.php',
// Если БД недоступна, то хотя бы запишет в файл
          'settings' => [
            'file' => 'local/Logs/OfcoderDBExceptionHandlerLog-' . date("Y-m-d") . '.txt',
            'log_size' => 1000000,
// Битрикс по умолчанию генерирует уйму, просто кучу исторических ошибок. Чтобы не забить, игнориуем один из типов
            'dont_show' => ['\Bitrix\Main\Diag\ExceptionHandlerLog::LOW_PRIORITY_ERROR']
          ],
        ],
      ),
    'readonly' => false,
  );
}


return array(
  'exception_handling' => $exceptionHandling
);