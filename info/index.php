<?php
  require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
  $APPLICATION->SetTitle("Тесты");
?>
<?php $APPLICATION->IncludeComponent('ofcoder:base.grid', '', []);
?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>