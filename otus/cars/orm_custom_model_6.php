<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->SetTitle('Вывод связанных полей');

use Models\Lists\CarsPropertyValuesTable as CarsTable;
use Ofcoder\Diag\Helper;

// вывод данных по списку записей из инфоблока Автомобили
$cars = CarsTable::getList([
  'select' => [
    'ID' => 'IBLOCK_ELEMENT_ID',
    'NAME' => 'ELEMENT.NAME',
    'MANUFACTURER_ID' => 'MANUFACTURER_ID',
    'CITY_ID' => 'CITY_ID',
  ]
])->fetchAll();

Helper::pr($cars);

$cars = CarsTable::query()
    ->setSelect([
        'NAME' => 'ELEMENT.NAME',
        'MANUFACTURER_NAME' => 'MANUFACTURER.ELEMENT.NAME',
        'CITY_NAME' => 'CITY.ELEMENT.NAME'
    ])
    ->registerRuntimeField(
        null,
        new \Bitrix\Main\Entity\ReferenceField('MANUFACTURER',
            \Models\Lists\CarManufacturerPropertyValuesTable::getEntity(),
        ['=this.MANUFACTURER_ID' => 'ref.IBLOCK_ELEMENT_ID']
        )
    )
    ->fetchAll();

Helper::pr($cars);



// добавление данных  записей в инфоблок Автомобили
$dbResult = CarsTable::add([
        'NAME'=>'BMW X5',
        'MANUFACTURER_ID'=>30,
        'CITY_ID'=>34,
        'MODEL'=>'X5',
        'ENGINE_VOLUME'=>'4',
        'PRODUCTION_DATE'=>date('d.m.Y H:i:s'),
]);
Helper::pr($dbResult);