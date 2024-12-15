<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_AGENT_STATISTIC', true);
define('STOP_STATISTICS', true);
define('BX_CRONTAB_SUPPORT', true);
define('LANGUAGE_ID', 'ru');

ini_set('memory_limit', '512M');

@set_time_limit(0);
@ignore_user_abort(true);

echo "<pre>";
print_r(getShopAddressProgram(47));
echo "</pre>";
$shopId = 47;

//var_dump(getShopAddressProgram(47));

$shops = getShopAddressProgram(47);

$file = $_SERVER["DOCUMENT_ROOT"] . '/upload/address/address.xls';
$str = "";
$str .= "<table border='1'>

	<tr>
	 <td>Номер магазина: {$shops[0]['UF_NUMBER']}</td>
   <td colspan='2'>Адрес: {$shops[0]['SHOP_ADDRESS']}</td>
	</tr>
   <tr>
     <td>Улица</td>
     <td>Дом</td>
     <td>Кол-во квартир</td>
   </tr>";

foreach ($shops as $shop)
{
    
    $str .= "
        <tr>
            <td>{$shop['UF_STREET']}</td>
            <td>{$shop['UF_HOUSE']}</td>
            <td>{$shop['UF_FLAT_COUNT']}</td>
      
        </tr>
    ";
}

$str .= "</table>";

header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=address.xls");
//echo $str;


$fp = fopen($file, 'w');
fwrite($fp, $str);
fclose($fp);

$arFiles = array($file);



//CEvent::Send("ORDERS_EXPORT", 's2', array(), "N", "", $arFiles);

//require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");

 use kb\Model\ShopsTable,
    kb\Model\ShopsApTable,
    kb\Model\ShopsApHistoryTable,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\ORM\Fields\ExpressionField,
    Bitrix\Main\Mail\Event,
    Bitrix\Main\Diag\Debug,
    Bitrix\Main\Loader,
    Bitrix\Main\Application;



function getShopAddressProgram($shopNumber)
    {
      \Bitrix\Main\Application::getConnection()->startTracker();
      
      $addressAr = ShopsApTable::getList([
        'filter' => [
          'UF_NUMBER' => $shopNumber,
        ],
        'select' => [
          '*',
          'SHOP_ADDRESS' => 'SHOP.UF_ADDRESS',
        ],
        'order' => ['ID'],
        //'cache' => ['ttl' => 3600],
        
        'runtime' => [
          new \Bitrix\Main\Entity\ReferenceField(
            'SHOP',
            'kb\Model\ShopsTable',
            array('=this.UF_NUMBER' => 'ref.UF_NUMBER')
          )
        ]
      ]);
      Debug::dumpToFile($addressAr, 'ADDRESS_AP', '/local/logs/class-'.date("Y-m-d").'.txt');
 
        return $addressAr->fetchAll();     
    }

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");
	
	