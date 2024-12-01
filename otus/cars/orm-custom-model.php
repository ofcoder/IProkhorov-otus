<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle('Вывод связанных полей');

use Models\Lists\CarsPropertyValuesTable as CarsTable;
use Ofcoder\Diag\Helper;

// вывод данных по списку записей из инфоблока Автомобили
/**/
$cars = CarsTable::getList([
		'select'=>[
          'ID'=>'IBLOCK_ELEMENT_ID',
          'NAME'=>'ELEMENT.NAME',
 		  'MANUFACTURER_ID'=>'MANUFACTURER_ID'
      ]
  ])->fetchAll();


 Helper::pr($cars);
//Helper::log2file($cars, '$cars-1', LOG_FILE_PATH);
 /**/

/**/
 $query = CarsTable::query()
    ->setSelect([
        '*',
        'NAME' => 'ELEMENT.NAME',
        'MANUFACTURER_NAME' => 'MANUFACTURER.ELEMENT.NAME',
        'CITY_NAME' => 'CITY.ELEMENT.NAME',
        'COUNTRY' => 'MANUFACTURER.COUNTRY', 
    ])
    ->setOrder(['COUNTRY' => 'desc'])
    ->registerRuntimeField(
        null,
        new \Bitrix\Main\Entity\ReferenceField(
            'MANUFACTURER',
            \Models\Lists\CarManufacturerPropertyValuesTable::getEntity(),
            ['=this.MANUFACTURER_ID' => 'ref.IBLOCK_ELEMENT_ID']
        )
    );
// посмотрим, какой запрос был сформирован
echo '<pre>' . $cars->getQuery() . '</pre>';
    $cars = $query->fetchAll();
//Helper::log2file($cars, '$cars-2', LOG_FILE_PATH);
Helper::pr($cars);

/**/

// добавление данных  записей в инфоблок Автомобили
$dbResult = CarsTable::add([
        'NAME'=>'TEST',
        'MANUFACTURER_ID'=>24,
        'CITY_ID'=>34,
        'MODEL'=>'X5',
        'ENGINE_VOLUME'=>'4',
        'PRODUCTION_DATE'=>date('d.m.Y'),
]);
Helper::pr($dbResult);
//Helper::log2file($dbResult, 'add_DbResult', LOG_FILE_PATH);

 /**/

// удаление записи из БД
$res = \Bitrix\Iblock\Elements\ElementcarsTable::delete(51);
//Helper::pr($res);
Helper::log2file($res, 'delete-$res', LOG_FILE_PATH);

// редактирование записей в БД
/**/
 \Bitrix\Main\Loader::IncludeModule("iblock");
// делаем запрос на изменение поля NAME в записи с ID 45
$res = \Bitrix\Iblock\Elements\ElementcarsTable::update(45, array(
    //'NAME' => 'TEST 777',
    'NAME' => 'BMW X5',
));
 //Helper::pr($res);
//Helper::log2file($res, 'update-$res', LOG_FILE_PATH);

/**/
$cars = \Bitrix\Iblock\Elements\ElementcarsTable::query()
    ->addSelect('NAME')
    ->addSelect('MODEL') // имя свойства 
    ->addSelect('ID')
    ->setFilter(array('=ID' => 45))
  /*
    ->registerRuntimeField(
      null,
      new \Bitrix\Main\Entity\ReferenceField(
        'MANUFACTURER',
        \Models\Lists\CarManufacturerPropertyValuesTable::getEntity(),
        ['=this.PROPERTY_64' => 'ref.IBLOCK_ELEMENT_ID']
      )
    )
  */
->fetchCollection();

foreach ($cars as $car) {
        $car->setModel('X5'); // изменение значения свойства MODEL
        $car->setName('BMW X5'); // изменение значения свойства MODEL
        //$car->setManufacturer(33); // изменение значения свойства MODEL
        $car->save(); // сохранение данных

}
// посмотрим, какой запрос был сформирован
//echo '<pre>' . $cars->getQuery() . '</pre>';
/**/