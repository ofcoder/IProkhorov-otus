<?php
  //https://aclips.ru/grid-component-bitrix24/
  //https://aclips.ru/scriptjs-component-bitrix24/
  require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
  $APPLICATION->SetTitle("Новый раздел");
?>
<?php $APPLICATION->IncludeComponent('ofcoder:base.grid', '', [], false); ?>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>