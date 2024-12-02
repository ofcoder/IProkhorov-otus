<?php

return array (
  'cache_flags' => 
  array (
    'value' => 
    array (
      'config_options' => 3600.0,
    ),
    'readonly' => false,
  ),
  'cookies' => 
  array (
    'value' => 
    array (
      'secure' => false,
      'http_only' => true,
    ),
    'readonly' => false,
  ),

  'exception_handling' => 
  array (
    'value' => 
    array (
      'debug' => true,
      'handled_errors_types' => 4437,
      'exception_errors_types' => 4437,
      'ignore_silence' => false,
      'assertion_throws_exception' => true,
      'assertion_error_type' => 256,
      'log' => NULL,
    ),
    'readonly' => false,
  ),

  'connections' => 
  array (
    'value' => 
    array (
      'default' => 
      array (
        'host' => 'localhost',
        'database' => 'ct93339_db',
        'login' => 'ct93339_db',
        'password' => '1q2w3e!Q@W#E',
        'options' => 2.0,
        'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
      ),
      // База данных для логов отдельная всегда
      'log_db' => [
        'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
        'host' => 'localhost',
        'database' => 'ct93339_log',
        'login' => 'ct93339_log',
        'password' => '1q2w3e!Q@W#E',
        'options' => 2.0
      ],
    ),
    'readonly' => true,
  ),
  'crypto' => 
  array (
    'value' => 
    array (
      'crypto_key' => 'cccb43b6365aa44aaf44988241e8e4d8',
    ),
    'readonly' => true,
  ),

  'smtp' => array (
    'value' => array(
      'enabled' => true,
      'debug' => true, //optional
      'log_file' => $_SERVER['DOCUMENT_ROOT'] . '/local/logs/ct93339_mailer.log', //optional
    ),
  ),
  'composer' => array (
    'value' => ['config_path' => $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/composer.json']
    ),
);
