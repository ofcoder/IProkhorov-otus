<?php

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->setTitle('Отладка SQL');

/************************** iblock *********************************/

Loader::includeModule('iblock');

Application::getConnection()->startTracker();

$query = \Bitrix\Iblock\ElementTable::getList([
  'filter' => [
    'IBLOCK_ID' => 1,
  ],
  'select' => [
    'ID'
  ],
]);

Application::getConnection()->stopTracker();
Debug::dump($query->getTrackerQuery()->getSql());

/************************** CRM ************************************/
Loader::includeModule('crm');

Application::getConnection()->startTracker();
$arSelect = ['ID'];
$arFilter =[
  'CATEGORY_ID' => 0,
  '<=DATE_CREATE' => \Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime('-1 month')),
];
$arDeals = \Bitrix\Crm\DealTable::getList([
  'filter' => $arFilter,
  'select' => $arSelect,
  'offset' => 0,
  'limit' => 10,
]
);
Application::getConnection()->stopTracker();
Debug::dump($arDeals->getTrackerQuery()->getSql());

/************************** hbL ************************************/

Loader::includeModule('highloadblock');

use \Bitrix\Highloadblock as HL;
use \Bitrix\Main\Enitity;

$hlbl = 1; // укажем id нашего highload блока
$hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch();

$entity = HL\HighloadBlockTable::compileEntity($hlblock);
$entityDataClass = $entity->getDataClass();

$dbData = $entityDataClass::getList([
  'select' => ['*'],
  'order' => ['ID' => 'ASC'],
])->fetchAll();

// Выводим записи на экран

Debug::dump($dbData);
Debug::dumpToFile($dbData, $dbData = "dbData", $fileName = 'DEBUG_FILE_NAME');

/********************* Diag\SqlTracker::getQueries *******************/

Loader::includeModule('highloadblock');

$hlbl = 1; // укажем id нашего highload блока
$hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch();
// делаем выборку хайлоуд блока по названию таблицы
$hlblock = HL\HighloadBlockTable::getList(
  array(
    "filter" => array(
      '=TABLE_NAME' => 'b_hlsys_marking_code_group',
      //'NAME' => 'ProductMarkingCodeGroup',
    )
  )
)->fetch();

$entity = HL\HighloadBlockTable::compileEntity($hlblock);
$entityDataClass = $entity->getDataClass();

$connection = Bitrix\Main\Application::getConnection();
/** Bitrix\Main\Diag\SqlTracker $tracker */
$tracker = $connection->startTracker();

$dbData = $entityDataClass::getList([
  'select' => ['*'],
  'order' => ['ID' => 'ASC'],
])->fetchAll();

$connection->stopTracker();

//Вывод Tracker
foreach ($tracker->getQueries() as $query) {
  echo "<br>=======================================================================<br><pre>\n";

  var_dump($query->getSql()); // Текст запроса
  var_dump($query->getTrace()); // Стек вызовов функций, которые привели к выполнению запроса
  var_dump($query->getTime()); // Время выполнения запроса в секундах
  echo "</pre>";
  // Debug::dumpToFile($var, $varName = "", $fileName = "");
  Debug::dumpToFile($query->getTime(), 'getTime', '/local/Logs/log-'.date("Y-m-d").'.txt');


}



/*********************************************************************/
//ORM: просмотр SQL запроса
//Пример для https://dev.1c-bitrix.ru/api_d7/bitrix/main/db/connection/starttracker.php

\Bitrix\Main\Application::getInstance()->getConnectionPool()->getConnection()->startTracker(true);
$result = \Bitrix\Crm\DealTable::getList([
  /* параметры выборки из ORM */
  'filter' => ['>ID' => 0],
  'select' => ['ID']
]);
// будет напечатан выбранный запрос
echo $result->getTrackerQuery()->getSql();

//--------------------------------------------------
//Просмотр запросов с помощью general_log
//Включаем логирование в таблицу:

//SET GLOBAL general_log = 'ON';
//SET GLOBAL log_output = 'TABLE';

//Выполняем действия на сайте, смотрим лог:

//SELECT * FROM mysql.general_log ORDER BY event_time DESC LIMIT 10;
/*********************************************************************/

