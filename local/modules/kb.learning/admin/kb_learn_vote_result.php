<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity;
use Bitrix\Vote\EventAnswerTable;
use Bitrix\Vote\EventTable;
use Bitrix\Vote\AnswerTable;
use Bitrix\Vote\QuestionTable;
use Bitrix\Vote\VoteTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if (!Loader::includeModule('vote') && !Loader::includeModule('kb.learning')) {
    die();
}

ClearVars();

$voteId = \Bitrix\Main\Config\Option::get(
    'kb.learning',
    'kb_learning_vote_id',
    "",
    false
);

if ($voteId <= 0) {
    $APPLICATION->SetTitle('Результаты опроса');
    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php'); // второй общий пролог
    ?>
    <p>Не выбран опрос по обучению.!</p>
    <a href="/bitrix/admin/settings.php?mid=kb.learning&lang=ru"><button>Выбрать</button></a>
<?php } else {

    $sTableID = EventAnswerTable::getTableName();
    $oSort = new \CAdminSorting($sTableID, "ID", "desc");
    $lAdmin = new \CAdminList($sTableID, $oSort);

    $filter = new \CAdminFilter(
        $sTableID."_filter",
        [
            'ID',
            'Город',
            'Пользователь',
            'Дата',
        ]
    );

    $arFilterFields = [
        'filter_user',
        'filter_user_type',
        'filter_id',
        'filter_date_start',
        'filter_date_end',
        'filter_user_city',
    ];


    $lAdmin->InitFilter($arFilterFields);// filter initialization

    $arFilter = [];

    $arFilter['VOTE_ID'] = $voteId;

    if (!empty($filter_id)) {
        $arFilter['=ID'] = $filter_id;
    }
    if (!empty($filter_date_from)) {
        $arFilter['>=DATE_VOTE'] = $filter_date_from;
    }
    if(!empty($filter_user_city)) {
        $arFilter['USER_CITY'] = $filter_user_city;
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
            $arFilter['<=DATE_VOTE'] = $filter_date_to;
        }
        else
        {
            $filter_date_to = '';
        }
    }

    $filterTypeMap = [
        'login' => 'USER.USER.LOGIN',
        'last_name' => 'USER.USER.LAST_NAME',
        'id' => 'USER.USER.ID',
        'name' => 'USER.USER.NAME',
    ];

    if(!empty($filter_user) && !empty($filter_user_type) && array_key_exists($filter_user_type, $filterTypeMap))
    {
        if ($filter_user_type == 'id') {
            $arFilter['=' . $filterTypeMap[$filter_user_type]] = $filter_user;
        } else {
            $arFilter['%=' . $filterTypeMap[$filter_user_type]] = '%'.$filter_user.'%';
        }
    }

    $nav = new \Bitrix\Main\UI\AdminPageNavigation("nav-vote-result");

// fetch data
    $voteResultList = EventTable::getList([
        'select' => [
            'ID',
            'VOTE_ID',
            'VOTE_NAME' => 'VOTE.TITLE',
            'USER_CITY' => 'USER.USER.WORK_CITY',
            'USER_FIO',
            'QUESTION_TEXT' => 'QUESTION_ENTITY.QUESTION',
            'ANSWER_TEXT' => 'ANSWER_ENTITY.MESSAGE',
            'QUESTION_ID' => 'QUESTION.QUESTION_ID',
            'ANSWER_ID' => 'QUESTION.ANSWER.ANSWER_ID',
            'DATE_VOTE',
        ],
        'filter' => $arFilter,
        'order' => [
            'ID' => 'DESC',
            'QUESTION.ID' => 'DESC',
            'QUESTION.ANSWER.ID' => 'DESC'
        ],
        'runtime' => [
            new ExpressionField(
                'USER_FIO',
                'concat(%s," ",%s," ",%s)',
                ['USER.USER.LAST_NAME', 'USER.USER.NAME', 'USER.USER.SECOND_NAME']
            ),
            new Reference(
                'QUESTION_ENTITY',
                QuestionTable::class,
                Join::on('this.QUESTION_ID', 'ref.ID')
            ),
            new Reference(
                'ANSWER_ENTITY',
                AnswerTable::class,
                Join::on('this.ANSWER_ID', 'ref.ID')
            ),
            new Reference(
                'VOTE',
                VoteTable::class,
                Join::on('this.VOTE_ID', 'ref.ID')
            ),
        ],
        'count_total' => true,
        'offset' => $nav->getOffset(),
        'limit' => $nav->getLimit(),
    ]);

    $nav->setRecordCount($voteResultList->getCount());
    $lAdmin->setNavigation($nav, 'Элементы');

// list header
    $lAdmin->AddHeaders([
        ['id' => 'ID', 'content' => 'ID', 'sort' => 'id', 'default' => true],
        //['id' => 'VOTE_ID', 'content' => 'VOTE_ID', 'sort' => 'id', 'default' => true],
        ['id' => 'VOTE_NAME', 'content' => 'Опрос', 'sort' => 'id', 'default' => true],
        ['id' => 'USER_CITY', 'content' => 'Город', 'default' => true],
        ['id' => 'USER_FIO', 'content' => 'Пользователь', 'default' => true],
        ['id' => 'QUESTION_TEXT', 'content' => 'Вопрос', 'default' => true],
        ['id' => 'ANSWER_TEXT', 'content' => 'Ответ', 'default' => true],
        ['id' => 'DATE_VOTE', 'content' => 'Дата ответа', 'default' => true],
    ]);

// building list
    while($voteResult = $voteResultList->fetch())
    {
        $row =& $lAdmin->AddRow($voteResult['ID'], $voteResult);

        $row->AddViewField('VOTE_NAME', "<a href=\"/bitrix/admin/vote_edit.php?lang=".LANGUAGE_ID."&ID=".$voteResult['VOTE_ID']."\">".$voteResult['VOTE_NAME']."</a>");
    }
// резюме таблицы
    $lAdmin->AddFooter(
        [
            ['title' => 'Кол-во элементов', 'value' => $voteResultList->getCount()], // кол-во элементов
            ['counter' => true, 'title' => 'Выбранно элементов', 'value' => '0'], // счетчик выбранных элементов
        ]
    );

// альтернативный вывод
    $lAdmin->AddAdminContextMenu([]);
    $lAdmin->CheckListMode();
// установим заголовок страницы
    $APPLICATION->SetTitle('Результаты опроса');


    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php'); // второй общий пролог
    ?>
    <form name="form1" method="GET" action="<?= $APPLICATION->GetCurPage(); ?>" onsubmit="return this.set_filter.onclick();">
        <?php $filter->Begin(); ?>
        <tr>
            <td>ID:</td>
            <td><input type="text" name="filter_id" value="<?= htmlspecialcharsbx($filter_id); ?>" size="47"></td>
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
}
?>
<br>
<span>Сменить опрос после обучения: </span>
<a href="/bitrix/admin/settings.php?mid=kb.learning"><button>Выбрать опрос</button></a>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');