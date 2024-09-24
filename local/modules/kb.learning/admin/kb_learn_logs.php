<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

use Kb\Learning\Entity\LogTable;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if (!Loader::includeModule('kb.learning')) {
    die();
}

ClearVars();

$sTableID = LogTable::getTableName();
$oSort = new \CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new \CAdminList($sTableID, $oSort);

$filter = new \CAdminFilter(
    $sTableID."_filter",
    [
        'ID',
        'Пользователь',
        'Дата',
    ]
);

$arFilterFields = [
    'filter_user',
    'filter_user_type',
    'filter_id',
    'filter_date_from',
    'filter_date_to',
];


$lAdmin->InitFilter($arFilterFields);// filter initialization

$arFilter = [];

if (!empty($filter_id)) {
    $arFilter['=ID'] = $filter_id;
}

if (!empty($filter_date_from))
{
    $arFilter['>=LOG_DATE'] = $filter_date_from;
}

if (!empty($filter_date_to))
{
    if($arDate = ParseDateTime($filter_date_to, CSite::GetDateFormat('FULL', SITE_ID)))
    {
        if(mb_strlen($filter_date_to) < 11)
        {
            $arDate['HH'] = 23;
            $arDate['MI'] = 59;
            $arDate['SS'] = 59;
        }

        $filter_date_to = date($DB->DateFormatToPHP(CSite::GetDateFormat('FULL', SITE_ID)), mktime($arDate['HH'], $arDate['MI'], $arDate['SS'], $arDate['MM'], $arDate['DD'], $arDate['YYYY']));
        $arFilter['<=LOG_DATE'] = $filter_date_to;
    }
    else
    {
        $filter_date_to = '';
    }
}

$filterTypeMap = [
    'login' => 'USER_LOGIN',
    'last_name' => 'USER_LAST_NAME',
    'id' => 'USER_ID',
    'name' => 'USER_NAME',
];

if(!empty($filter_user) && !empty($filter_user_type) && array_key_exists($filter_user_type, $filterTypeMap))
{
    $arFilter['=' . $filterTypeMap[$filter_user_type]] = $filter_user;
}

// fetch data
$rsData = LogTable::GetList([
    'filter' => $arFilter,
    'order' => ['ID' => 'DESC']
]);

$rsData = new \CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation string setup
$lAdmin->NavText($rsData->GetNavPrint('Элементы'));


// list header
$lAdmin->AddHeaders([
    ['id' => 'ID', 'content' => 'ID', 'sort' => 'id', 'default' => true],
    ['id' => 'USER_ID', 'content' => 'Пользователь', 'default' => true],
    ['id' => 'DESCRIPTION', 'content' => 'Описание', 'default' => true],
    ['id' => 'LOG_DATE', 'content' => 'Дата', 'default' => true],
]);

// building list
while($arRes = $rsData->NavNext(true, 'f_'))
{
    $row =& $lAdmin->AddRow($f_ID, $arRes);
    $userFIO = \Bitrix\Main\UserTable::getList([
        'filter' => ['ID' => $f_USER_ID],
        'select' => ['ID','SECOND_NAME', 'NAME', 'LAST_NAME', 'LOGIN'],
        'limit' => 1
    ])->fetch();
    $userFIO = sprintf('[%s] (%s) %s %s', $userFIO['ID'], $userFIO['LOGIN'], $userFIO['NAME'], $userFIO['LAST_NAME']);
    $row->AddViewField('USER_ID', $userFIO);
}
// резюме таблицы
$lAdmin->AddFooter(
    [
        ['title' => 'Кол-во элементов', 'value' => $rsData->SelectedRowsCount()], // кол-во элементов
        ['counter' => true, 'title' => 'Выбранно элементов', 'value' => '0'], // счетчик выбранных элементов
    ]
);

// альтернативный вывод
$lAdmin->AddAdminContextMenu([]);
$lAdmin->CheckListMode();
// установим заголовок страницы
$APPLICATION->SetTitle('Логи по изменениям элементов обучения');


require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php'); // второй общий пролог
?>
<form name="form1" method="GET" action="<?= $APPLICATION->GetCurPage(); ?>" onsubmit="return this.set_filter.onclick();">
<?php $filter->Begin(); ?>

	<tr>
		<td><b>Пользователь:</b></td>
		<td>
			<input type="text" name="filter_user" value="<?= htmlspecialcharsbx($filter_user); ?>" size="25">

			<?= SelectBoxFromArray(
				'filter_user_type',
				[
					'reference' => [
						'USER_LOGIN',
						'USER_LAST_NAME',
						'ID',
						'USER_NAME'
					],
					'reference_id' => [
						'login',
						'last_name',
						'id',
						'name',
					]
				],
				$filter_user_type,
				'',
				''
			);?>

		</td>
	</tr>


	<tr>
		<td>ID:</td>
		<td><input type="text" name="filter_id" value="<?= htmlspecialcharsbx($filter_id); ?>" size="47"></td>
	</tr>


    <tr>
        <td>Дата:</td>
        <td>
            <?= CalendarPeriod(
                "filter_date_from",
                htmlspecialcharsbx($filter_date_from),
                "filter_date_to",
                htmlspecialcharsbx($filter_date_to),
                "find_form",
                "Y"
            ); ?>
        </td>
    </tr>

<?php
$filter->Buttons(['table_id' => $sTableID, 'url' => $APPLICATION->GetCurPage(), 'form' => 'form1']);
$filter->End();
?>
</form>

<?php
$lAdmin->DisplayList();

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');