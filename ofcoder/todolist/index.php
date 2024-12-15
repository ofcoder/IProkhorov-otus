<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Задачи список");
?>

<?php $APPLICATION->IncludeComponent(
  "ofcoder:todolist", ".default", [
  'HIGHLOAD_BLOCK' => 'TodoList',
  'ADDED_TASK_NOTIFICATION_TEXT' => 'Задача успешно добавлена',
],
  false
);
?>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>