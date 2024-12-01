<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use Ofcoder\Diag\Helper;

$APPLICATION->SetTitle('Вывод связанных полей');

use Bitrix\Main\Loader;
use Bitrix\Iblock\Iblock;
Loader::includeModule('iblock');

$iblockId = 16;
$iblockElementId = 36;

// Old API
/**/
//Получить список элементов
//https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockelement/getlist.php

$arFilter = ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'];
$arSelect = ['ID', 'NAME', 'CODE', 'PROPERTY_MODEL'];
$res = CIBlockElement::GetList([], $arFilter, false, [], $arSelect);
while($arFields = $res->fetch()){
  Helper::pr($arFields);
}


$arFilter = ['IBLOCK_ID' => $iblockId];
$arSelect = ['NAME'];
$rsSect = CIBlockSection::GetList(['left_margin' => 'asc'], $arFilter, false, $arSelect, false);
while ($arSect = $rsSect->fetch())
{
  Helper::pr($arSect);
}

/*
//Добавление элементов
//https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockelement/add.php
$arElementProps = [
    'MODEL' => 'X5',
];

$arIblockFields = [
    'IBLOCK_ID' => $iblockId,
    'NAME' => 'New element',
    'PROPERTY_VALUES' => $arElementProps
];
$objIblockElement = new \CIBlockElement();
$objIblockElement->Add($arIblockFields);

/**/



// ORM

//get by id
/**/
//Надо сделать символьный код инфоблока
//Надо свойства хранить в отдельной таблице
$iblock = Iblock::wakeUp($iblockId);
$element = $iblock->getEntityDataClass()::getByPrimary($iblockElementId)->fetchObject();

// get props
$element = $iblock->getEntityDataClass()::getByPrimary(
	$iblockElementId,
	['select' => ['NAME', 'MODEL', 'MANUFACTURER_ID']])
->fetchObject();

$name = $element->get('NAME');
echo 'NAME: ';
Helper::pr($name);

$model = $element->get('MODEL')->getValue();
echo 'MODEL: ';
Helper::pr($model);

/*
// Свойство типа файл
'MORE_PHOTO.FILE',
// Свойство типа список
'NEWPRODUCT.ITEM',
// Свойство типа привязка к элементу инфоблока
'RECOMMEND.ELEMENT',
// Свойство типа привязка к разделу инфоблока
'NEWS_SECTION.SECTION'
*/
//https://hmarketing.ru/blog/bitrix/rabota-s-elementami-infoblokov-cherez-orm/
$manufact = $element->get('MANUFACTURER_ID')->getValue();
echo 'MANUFACTURER_ID: ';
Helper::pr($manufact,true);
/**/


// get list
/**/
 $elements = \Bitrix\Iblock\Elements\ElementCarsTable::getList([ // cars - cимвольный код API инфоблока
    'select' => ['MODEL'], // имя свойства
])->fetchCollection();

foreach ($elements as $element) {
    Helper::pr('MODEL - '.$element->getModel()->getValue()); // получение значения свойства MODEL
}

// получение через query списка элементов
$elements = \Bitrix\Iblock\Elements\ElementCarsTable::query() // cars - cимвольный код API инфоблока
    ->addSelect('NAME')
    ->addSelect('MODEL') // имя свойства
    ->addSelect('ID')
    ->fetchCollection();

foreach ($elements as $key => $item) {
    Helper::pr($item->getName().' '.$item->getModel()->getValue()); // получение значения свойства MODEL
    // $value = $item->getModel()->getValue();
    // if($value == 'Q7'){
    //         $item->setModel('Q7 TEST'); // изменение значения свойства MODEL
    //         $item->save(); // сохранение данных
    // }
}
/**/


