<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity;

use Kb\Learning\Entity\TestResultTable;
use Kb\Learning\Entity\AnswerTable;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if (!Loader::includeModule('kb.learning')) {
    die();
}
if (!Loader::includeModule('learning')) {
    die();
}

ClearVars();

$sTableID = TestResultTable::getTableName();
$oSort = new \CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new \CAdminList($sTableID, $oSort);

$filter = new \CAdminFilter(
    $sTableID."_filter",
    [
        'ID',
        'Пользователь',
        'Дата',
        'Тест',
        'Правильный ответ?',
        'Город'
    ]
);

$arFilterFields = [
    'filter_user',
    'filter_user_type',
    'filter_id',
    'filter_date_start',
    'filter_date_end',
    'filter_test_id',
    'filter_is_correct_answer',
    'filter_user_city',
];


$lAdmin->InitFilter($arFilterFields);// filter initialization

$arFilter = [
    'ANSWERED' => 'Y',
    '!RESPONSE' => false,
];

if (!empty($filter_id)) {
    $arFilter['=ID'] = $filter_id;
}

if (!empty($filter_date_from)) {
    $arFilter['>=DATE_INSERT'] = $filter_date_from;
}
if(!empty($filter_test_id)) {
    $arFilter['TEST_ID'] = $filter_test_id;
}
if(!empty($filter_is_correct_answer)) {
    $arFilter['CORRECT'] = $filter_is_correct_answer;
}
if(!empty($filter_user_city)) {
    $arFilter['STUDENT_CITY'] = $filter_user_city;
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

        $filter_date_end = date($DB->DateFormatToPHP(CSite::GetDateFormat('FULL', SITE_ID)), mktime($arDate['HH'], $arDate['MI'], $arDate['SS'], $arDate['MM'], $arDate['DD'], $arDate['YYYY']));
        $arFilter['<=DATE_INSERT'] = $filter_date_to;
    }
    else
    {
        $filter_date_to = '';
    }
}

$filterTypeMap = [
    'login' => 'ATTEMPT.STUDENT.LOGIN',
    'last_name' => 'ATTEMPT.STUDENT.LAST_NAME',
    'id' => 'ATTEMPT.STUDENT.ID',
    'name' => 'ATTEMPT.STUDENT.NAME',
    'xml_id' => 'ATTEMPT.STUDENT.XML_ID',
];

if(!empty($filter_user) && !empty($filter_user_type) && array_key_exists($filter_user_type, $filterTypeMap))
{
    $arFilter['=' . $filterTypeMap[$filter_user_type]] = $filter_user;
}

$nav = new \Bitrix\Main\UI\AdminPageNavigation("nav-test-result");

// fetch data
$testResultList = TestResultTable::GetList([
    'select' => [
        'ID',
        'TEST_ID' => 'ATTEMPT.TEST_ID',
        'TEST_NAME' => 'ATTEMPT.TEST.NAME',
        'STUDENT_FIO',
        'STUDENT_CITY' => 'ATTEMPT.STUDENT.WORK_CITY',
        'STUDENT_QR' => 'ATTEMPT.STUDENT.XML_ID',
        'STUDENT_POSITION' => 'ATTEMPT.STUDENT.WORK_POSITION',
        'DATE_INSERT',
        'QUESTION_NAME' => 'QUESTION.NAME',
        'RESPONSE',
        'CORRECT',
        'LINKED_LESSON_ID' => 'ATTEMPT.TEST.COURSE.LINKED_LESSON_ID',
        'COURSE_ID' => 'ATTEMPT.TEST.COURSE.ID',
    ],
    'filter' => $arFilter,
    'order' => ['ID' => 'DESC'],
    'runtime' => [
        new ExpressionField(
            'STUDENT_FIO',
            'concat(%s," ",%s," ",%s)',
            ['ATTEMPT.STUDENT.LAST_NAME', 'ATTEMPT.STUDENT.NAME', 'ATTEMPT.STUDENT.SECOND_NAME']
        ),
    ],
    'count_total' => true,
    'offset' => $nav->getOffset(),
    'limit' => $nav->getLimit(),
]);

$nav->setRecordCount($testResultList->getCount());
$lAdmin->setNavigation($nav, 'Элементы');

// list header
$lAdmin->AddHeaders([
    ['id' => 'ID', 'content' => 'ID', 'sort' => 'id', 'default' => true],
    ['id' => 'STUDENT_CITY', 'content' => 'Город', 'default' => true],
    ['id' => 'STUDENT_QR', 'content' => 'Штрихкод сотрудника', 'default' => true],
    //['id' => 'STUDENT_POSITION', 'content' => 'Должность', 'default' => true], //должность сотрудника
    ['id' => 'STUDENT_FIO', 'content' => 'Пользователь', 'default' => true],
    ['id' => 'TEST_ID', 'content' => 'Тест', 'sort' => 'id', 'default' => true],
    ['id' => 'DATE_INSERT', 'content' => 'Дата ответа', 'default' => true],
    ['id' => 'QUESTION_NAME', 'content' => 'Вопрос', 'default' => true],
    //['id' => 'RESPONSE', 'content' => 'Ответ id', 'default' => true],
    ['id' => 'ANSWER', 'content' => 'Ответ', 'default' => true],
    ['id' => 'CORRECT', 'content' => 'Правильный ответ?', 'default' => true],
]);

// building list
while($testResult = $testResultList->fetch())
{
    $row =& $lAdmin->AddRow($testResult['ID'], $testResult);;

    $row->AddViewField('CORRECT', $testResult['CORRECT'] == 'Y' ? 'Да' : 'Нет');

    $row->AddViewField('TEST_ID', "<a href=\"/bitrix/admin/learn_test_edit.php?lang=".LANGUAGE_ID."&COURSE_ID=".$testResult['COURSE_ID']."&PARENT_LESSON_ID=".$testResult['LINKED_LESSON_ID']."&LESSON_PATH=".$testResult['LINKED_LESSON_ID']."&ID=".$testResult['TEST_ID']."&filter=Y&set_filter=Y\">".$testResult['TEST_NAME']."</a>");

    $answerIds = explode(',', $testResult['RESPONSE']);
    $answerRes = AnswerTable::getList([
        'select' => ['ID', 'ANSWER'],
        'filter' => ['ID' => $answerIds],
    ]);
    $answer = '';
    while ($arAnswer = $answerRes->fetch()) {
        $answer .= $arAnswer['ANSWER'] . '<br>';
    }
    $row->AddViewField('ANSWER', $answer);
}
// резюме таблицы
$lAdmin->AddFooter(
    [
        ['title' => 'Кол-во элементов', 'value' => $testResultList->getCount()], // кол-во элементов
        ['counter' => true, 'title' => 'Выбранно элементов', 'value' => '0'], // счетчик выбранных элементов
    ]
);

// альтернативный вывод
$lAdmin->AddAdminContextMenu([]);
$lAdmin->CheckListMode();
// установим заголовок страницы
$APPLICATION->SetTitle('Результаты тестирования');


require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php'); // второй общий пролог
?>
<form name="form1" method="GET" action="<?= $APPLICATION->GetCurPage(); ?>" onsubmit="return this.set_filter.onclick();">
<?php $filter->Begin(); ?>

    <tr>
        <td>ID:</td>
        <td><input type="text" name="filter_id" value="<?= htmlspecialcharsbx($filter_id); ?>" size="47"></td>
    </tr>

    <tr>
		<td><b>Пользователь:</b></td>
		<td>
			<input type="text" name="filter_user" value="<?= htmlspecialcharsbx($filter_user); ?>" size="25">

			<?= SelectBoxFromArray(
				'filter_user_type',
				[
					'reference' => [
						'login/email',
						'Фамилия',
						'id',
						'Имя',
                        'Штрихкод'
					],
					'reference_id' => [
						'login',
						'last_name',
						'id',
						'name',
                        'xml_id',
					]
				],
				$filter_user_type,
				'',
				''
			);?>

		</td>
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

    <tr>
        <td>Тест:</td>
        <td>
            <select name="filter_test_id">
                <option value="">все</option>
                <?
                $l = \CTest::GetList(Array(), Array());
                while($l->ExtractFields("l_")):
                    ?><option value="<?echo $l_ID?>"<?if($filter_test_id==$l_ID)echo " selected"?>><?echo $l_NAME?></option><?
                endwhile;
                ?>
            </select>
        </td>
    </tr>

    <tr>
        <td>Правильный ответ?:</td>
        <td>
            <select name="filter_is_correct_answer">
                <option value="">все</option>
                <option value="Y">Да</option>
                <option value="N">Нет</option>
            </select>
        </td>
    </tr>

    <tr>
        <td>Город:</td>
        <td>
            <select name="filter_user_city">
                <option value="">все</option>
                <?php
                $cities = \Bitrix\Main\UserTable::GetList([
                    'select' => [new Entity\ExpressionField('CITY_NAME',
                        'DISTINCT %s', ['WORK_CITY']
                    )],
                    'filter' => ['!WORK_CITY' => false],
                ]);
                while($city = $cities->fetch()): ?>
                    <option value="<?= $city['CITY_NAME']; ?>"<?php if($filter_user_city == $city['CITY_NAME']) echo " selected"?>><?= $city['CITY_NAME']; ?></option>
                <?php endwhile; ?>
            </select>
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