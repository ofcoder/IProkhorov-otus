<?php //https://habr.com/ru/articles/810581/
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle('Доктора и процедуры');
?>

<?php $APPLICATION->IncludeComponent('ofcoder:doctors.list', '', );?>
<?php //$APPLICATION->IncludeComponent('ofcoder:base.ymap', '', );?>

<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');?>