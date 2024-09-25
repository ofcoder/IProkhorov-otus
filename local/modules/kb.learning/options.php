<?php
// пространство имен для подключений ланговых файлов
use Bitrix\Main\Localization\Loc;
// пространство имен для получения ID модуля
use Bitrix\Main\HttpApplication;
// пространство имен для загрузки необходимых файлов, классов, модулей
use Bitrix\Main\Loader;
// пространство имен для работы с параметрами модулей хранимых в базе данных
use Bitrix\Main\Config\Option;
// подключение ланговых файлов
Loc::loadMessages(__FILE__);
// получение запроса из контекста для обработки данных
$request = HttpApplication::getInstance()->getContext()->getRequest();
// получаем id модуля
$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);
// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT < "R") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}
// подключение модуля
Loader::includeModule($module_id);
Loader::includeModule('vote');

$voteRes = \Bitrix\Vote\VoteTable::getList(['select' => ['ID', 'TITLE']]);

$votes = ['0' => 'Не выбрано'];

while ($vote = $voteRes->fetch()) {
    $votes[$vote['ID']] = $vote['TITLE'];
}

// настройки модуля для админки в том числе значения по умолчанию
$aTabs = array(
    array(
        // значение будет вставленно во все элементы вкладки для идентификации (используется для javascript)
        "DIV" => "edit1",
        // название вкладки в табах
        "TAB" => "Общие",
        // заголовок и всплывающее сообщение вкладки
        "TITLE" => "Общие настройки расширения модуля обучения",
        // массив с опциями секции
        "OPTIONS" => array(
            "Опрос после завершения курса",
            array(
                // имя элемента формы, для хранения в бд
                "kb_learning_vote_id",
                // поясняющий текст
                "Выберите опрос",
                // значение по умолчани, значение selectbox по умолчанию
                "",
                // тип элемента формы "select"
                ["selectbox", $votes]
            ),
        )
    ),
    array(
        // значение будет вставленно во все элементы вкладки для идентификации (используется для javascript)
        "DIV"   => "edit2",
        // название вкладки в табах из основного языкового файла битрикс
        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
        // заголовок и всплывающее сообщение вкладки из основного языкового файла битрикс
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")
    )
);
// проверяем текущий POST запрос и сохраняем выбранные пользователем настройки
if ($request->isPost() && check_bitrix_sessid()) {
    // цикл по вкладкам
    foreach ($aTabs as $aTab) {
        // цикл по заполненым пользователем данным
        foreach ($aTab["OPTIONS"] as $arOption) {
            // если это название секции, переходим к следующий итерации цикла
            if (!is_array($arOption)) {
                continue;
            }
            // проверяем POST запрос, если инициатором выступила кнопка с name="Update" сохраняем введенные настройки в базу данных
            if ($request["Update"]) {
                // получаем в переменную $optionValue введенные пользователем данные
                $optionValue = $request->getPost($arOption[0]);
                // устанавливаем выбранные значения параметров и сохраняем в базу данных, хранить можем только текст, значит если приходит массив, то разбиваем его через запятую, если не массив сохраняем как есть
                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
            }
            // проверяем POST запрос, если инициатором выступила кнопка с name="default" сохраняем дефолтные настройки в базу данных
            if ($request["default"]) {
                // устанавливаем дефолтные значения параметров и сохраняем в базу данных
                Option::set($module_id, $arOption[0], $arOption[2]);
            }
        }
    }
}
// отрисовываем форму, для этого создаем новый экземпляр класса CAdminTabControl, куда и передаём массив с настройками
$tabControl = new CAdminTabControl(
    "tabControl",
    $aTabs
);
// отображаем заголовки закладок
$tabControl->Begin();
?>
    <form action="<? echo ($APPLICATION->GetCurPage()); ?>?mid=<? echo ($module_id); ?>&lang=<? echo (LANG); ?>" method="post">
        <? foreach ($aTabs as $aTab) {
            if ($aTab["OPTIONS"]) {
                // завершает предыдущую закладку, если она есть, начинает следующую
                $tabControl->BeginNextTab();
                // отрисовываем форму из массива
                __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
            }
        }
        // завершает предыдущую закладку, если она есть, начинает следующую
        $tabControl->BeginNextTab();
        // выводим форму управления правами в настройках текущего модуля
        require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php";
        // подключаем кнопки отправки формы
        $tabControl->Buttons();
        // выводим скрытый input с идентификатором сессии
        echo (bitrix_sessid_post());
        // выводим стандартные кнопки отправки формы
        ?>
        <input class="adm-btn-save" type="submit" name="Update" value="Применить" />
        <input type="submit" name="default" value="По умолчанию" />
    </form>

    <a href="/bitrix/admin/kb_learn_vote_result.php?lang=ru">Смотреть результаты опроса</a>
<?php
// обозначаем конец отрисовки формы
$tabControl->End();