<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';

/*********************************************************************/
// Логирование агентов

//Этот кусок кода можно вставить, например в dbconn.php
if (!defined('BX_AGENTS_LOG_FUNCTION')) {
  define('BX_AGENTS_LOG_FUNCTION', 'OlegproAgentsLogFunction');

  function OlegproAgentsLogFunction($arAgent, $point)
  {
    $allowedAgentNames = [
      'CCatalogExport::PreGenerateExport',
    ];

    $isAllowAgent = false;

    if (isset($arAgent['NAME'])) {

      foreach ($allowedAgentNames as $allowedAgentName) {
        if (strpos($arAgent['NAME'], $allowedAgentName) !== false) {
          $isAllowAgent = true;

          break;
        }
      }

    }

    if ($isAllowAgent) {
      @file_put_contents(
        $_SERVER['DOCUMENT_ROOT'] . '/agents_executions_points.log',
        (
          PHP_EOL . date('d-m-Y H:i:s') . PHP_EOL .
          print_r($point, 1) . PHP_EOL .
          print_r($arAgent, 1) . PHP_EOL
        ),
        FILE_APPEND
      );
    }

  }

}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
