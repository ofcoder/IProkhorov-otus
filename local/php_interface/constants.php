<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
  die();
}
define('DEBUG_FILE_NAME', $_SERVER["DOCUMENT_ROOT"] .'/local/logs/'.date("Y-m-d").'.log');