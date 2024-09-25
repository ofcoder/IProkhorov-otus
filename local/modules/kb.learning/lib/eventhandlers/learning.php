<?php

namespace Kb\Learning\EventHandlers;

use Kb\Learning\Entity\LogTable;

class Learning
{
    const LEARN_EVENTS = [
        'OnAfterLessonAdd', // &$arFields
        'OnBeforeLessonDelete', // $id (Придется получить урок до удаления)
        'OnBeforeLessonUpdate', // &$arFields (Придется получить урок до обновления)
        'OnAfterTestAdd', // $arFields
        'OnBeforeTestDelete', // $id (Придется получить тест до удаления)
        'OnBeforeTestUpdate', // $arFields, $id (Придется получить тест до обновления)
        'OnAfterQuestionAdd', // $id, $arFields
        'OnAfterQuestionDelete', // $id, $arQuestion даже после удаления передаются
        'OnAfterQuestionUpdate', // $id, $arFields (Знаем только поля после изменения, то что было - уже не узнать)
    ];

    public static function OnAfterLessonAdd(&$arFields): void
    {
        global $USER;
        LogTable::Add([
            'USER_ID' => $USER->GetID(),
            'DESCRIPTION' => 'Добавил Урок'
        ]);
    }

    public static function OnBeforeLessonDelete($id): void
    {
        global $USER;
        LogTable::Add([
            'USER_ID' => $USER->GetID(),
            'DESCRIPTION' => 'Удалил Урок'
        ]);
    }

    public static function OnBeforeLessonUpdate(&$arFields): void
    {
        global $USER;
        LogTable::Add([
            'USER_ID' => $USER->GetID(),
            'DESCRIPTION' => 'Обновил Урок'
        ]);
    }

    public static function OnAfterTestAdd($arFields): void
    {
        global $USER;
        LogTable::Add([
            'USER_ID' => $USER->GetID(),
            'DESCRIPTION' => 'Добавил Тест'
        ]);
    }

    public static function OnBeforeTestDelete($id): void
    {
        global $USER;
        LogTable::Add([
            'USER_ID' => $USER->GetID(),
            'DESCRIPTION' => 'Удалил Тест'
        ]);
    }

    public static function OnBeforeTestUpdate($arFields, $id): void
    {
        global $USER;

        $data = json_encode([
            'action' => 'Обновление теста',
            'data' => $arFields
        ]);

        LogTable::Add([
            'USER_ID' => $USER->GetID(),
            'DESCRIPTION' =>  $data
        ]);
    }

    public static function OnAfterQuestionAdd($id, $arFields): void
    {
        global $USER;
        LogTable::Add([
            'USER_ID' => $USER->GetID(),
            'DESCRIPTION' => 'Добавил Вопрос'
        ]);
    }

    public static function OnAfterQuestionDelete($id, $arQuestion): void
    {
        global $USER;
        LogTable::Add([
            'USER_ID' => $USER->GetID(),
            'DESCRIPTION' => 'Удалил Вопрос'
        ]);
    }

    public static function OnAfterQuestionUpdate($id, $arFields): void
    {
        global $USER;
        LogTable::Add([
            'USER_ID' => $USER->GetID(),
            'DESCRIPTION' => 'Обновил Вопрос'
        ]);
    }
}