<?php

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if (!Loader::includeModule('kb.learning')) {
    die();
}
if (!Loader::includeModule('learning')) {
    die();
}

$request = Context::getCurrent()->getRequest();

$APPLICATION->SetTitle('Копирование теста');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

ClearVars();

if (!isset($request['COURSE_ID']) || !isset($request['PARENT_LESSON_ID'])) {
    throw new SystemException('Не указаны COURSE или LESSON ID');
}

if (isset($request['TEST_ID']) && (int) $request['TEST_ID'] > 0): ?>
    <?php
    $test = '';
    $questions = [];
    $answers = [];
    $testRes = \CTest::GetList([], ['ID' => $request['TEST_ID']]);

    if ($testFields = $testRes->fetch()) {
        if (!isset($request['LESSON_ID']) || $request['LESSON_ID'] <= 0) {
            throw new SystemException('Не указан ID урока в который записать вопросы теста');
        }

        $questionsFrom = $testFields['QUESTIONS_FROM_ID'];

        // Копирование Теста (переопределяем необходимые поля перед добавлением)
        $testFields['COURSE_ID'] = $request['COURSE_ID'];
        $testFields['NAME'] .= ' (Копия)';
        $testFields['QUESTIONS_FROM'] = 'S';
        $testFields['QUESTIONS_FROM_ID'] = $request['LESSON_ID'];
        unset($testFields['TIMESTAMP_X']);

        $test = new \CTest;
        $newTestId = $test->add($testFields);

        if (!$newTestId) {
            throw new SystemException('Не удалось создать копию теста');
        }
        // Получаем список вопросов для теста, проходимся по нему циклом, и также создаем для каждого вопроса, ответы
        $qRes = \CLQuestion::GetList([], ['LESSON_ID' => $questionsFrom]);

        while ($arQuestion = $qRes->fetch()) {
            // Копирование Вопроса (переопределяем необходимые поля перед добавлением)
            $questionId = $arQuestion['ID'];
            $arQuestion['LESSON_ID'] = $request['LESSON_ID'];
            unset($arQuestion['TIMESTAMP_X']);

            $question = new \CLQuestion;
            $newQuestionId = $question->add($arQuestion);

            if ($newQuestionId >= 0) {
                $aRes = \CLAnswer::GetList([],['QUESTION_ID' => $questionId]);
                while ($arAnswer = $aRes->fetch()) {
                    // Копирование Ответа (переопределяем необходимые поля перед добавлением)
                    $arAnswer['QUESTION_ID'] = $newQuestionId;
                    $answer = new \CLAnswer;
                    $newAnswerId = $answer->Add($arAnswer);
                }
            }
        }
        $copyResultMessage = "Тест {$request['TEST_ID']} скопирован" . '<br>';
        $copyResultMessage .= "ID нового теста: $newTestId";
    } else {
        throw new SystemException('Тест не найден или не существует!');
    }
    ?>
    <p><?= $copyResultMessage; ?></p>
    <p>Вернуться к списку тестов курса:</p>
    <p><a href="/bitrix/admin/learn_test_admin.php?COURSE_ID=<?= $request['COURSE_ID']; ?>&PARENT_LESSON_ID=<?= $request['PARENT_LESSON_ID']; ?>&LESSON_PATH=<?= $request['LESSON_PATH']; ?>"><button>Список тестов</button></a></p>
<?php else: ?>
    <?php
    $oTree = \CLearnLesson::GetTree($request['PARENT_LESSON_ID'], ['EDGE_SORT' => 'asc'], [], ['LESSON_ID', 'NAME']);
    $arSubLessons = $oTree->GetTreeAsList();
    if (!empty($arSubLessons)):
        $courses = [];
        $courseRes = \CCourse::GetList([],[],[]);
        while($courseRes->ExtractFields("c_")) {
            $courses[$c_ID]['NAME'] = $c_NAME;
        }
        ?>
        <form name="form1" method="post" action="<?= $APPLICATION->GetCurPage(); ?>">
            <input type="hidden" name="COURSE_ID" value="<?= $request['COURSE_ID']; ?>">
            <input type="hidden" name="PARENT_LESSON_ID" value="<?= $request['PARENT_LESSON_ID']; ?>">
            <input type="hidden" name="LESSON_PATH" value="<?= $request['LESSON_PATH']; ?>">
            <p>Выберите тест для копирования:</p>
            <select name="TEST_ID">
                <?php
                $l = \CTest::GetList([], []);
                while($l->ExtractFields("l_")):
                    ?><option value="<?= $l_ID; ?>"><?= $courses[$l_COURSE_ID]['NAME'] . '/' . $l_NAME; ?></option>
                <?php endwhile; ?>
            </select>
                <p>Выберите в какой урок скопировать вопросы теста:</p>
                <select name="LESSON_ID">
                    <?php foreach ($arSubLessons as $arSubLesson): ?>
                        <option value="<?= $arSubLesson['LESSON_ID']; ?>"><?= $arSubLesson['NAME']; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Копировать</button>
        </form>
    <?php else: ?>
        <p>В данном курсе отсутствуют уроки, создайте хотя бы один урок: <a href="/bitrix/admin/learn_unilesson_admin.php?lang=ru&PARENT_LESSON_ID=<?= $request['PARENT_LESSON_ID']; ?>&LESSON_PATH=<?= $request['LESSON_PATH']; ?>">Список уроков курса</a></p>
        <p>Вернуться : <a href="/bitrix/admin/learn_test_admin.php?COURSE_ID=<?= $request['COURSE_ID']; ?>&PARENT_LESSON_ID=<?= $request['PARENT_LESSON_ID']; ?>&LESSON_PATH=<?= $request['LESSON_PATH']; ?>"><button>Список тестов</button></a></p>
    <?php endif; ?>
<?php endif; ?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');