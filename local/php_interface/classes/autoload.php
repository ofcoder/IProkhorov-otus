<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
  die();
}

/**
 * - /local/php_interface/classes/{Path|raw}/{*|raw}.php
 * - /local/php_interface/classes/{Path|ucfirst,lowercase}/{*|ucfirst,lowercase}.php
 */
spl_autoload_register(function($sClassName)
{
  $sClassFile = __DIR__;

  if ( file_exists($sClassFile.'/'.str_replace('\\', '/', $sClassName).'.php') )
  {
    require_once($sClassFile.'/'.str_replace('\\', '/', $sClassName).'.php');
    return;
  }

  $arClass = explode('\\', strtolower($sClassName));
  foreach($arClass as $sPath )
  {
    $sClassFile .= '/'.ucfirst($sPath);
  }
  $sClassFile .= '.php';
  if (file_exists($sClassFile))
  {
    require_once($sClassFile);
  }
});