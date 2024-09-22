<?php
/**
 * Project bootstrap files
 */
foreach( [
           /**
            * File constants
            */
           __DIR__.'/constants.php',

           /**
            * Events subscribe
            */
           __DIR__.'/events.php',

           /**
            * Include composer libraries
            */
           __DIR__.'/vendor/autoload.php',
            /**
            * Include custom classes
            */
           __DIR__.'/classes/autoload.php',

         ]
         as $filePath )
{
  if ( file_exists($filePath) )
  {
    require_once($filePath);
  }
}
unset($filePath);

\Ofcoder\Diagnostic\Helper::log2file('TEST');
?>