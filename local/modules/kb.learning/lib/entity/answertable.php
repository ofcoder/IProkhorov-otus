<?php
namespace Kb\Learning\Entity;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class AnswerTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> QUESTION_ID int mandatory
 * <li> SORT int optional default 10
 * <li> ANSWER text mandatory
 * <li> CORRECT string(1) mandatory
 * <li> FEEDBACK text optional
 * <li> MATCH_ANSWER text optional
 * </ul>
 *
 * @package Bitrix\Learn
 **/

class AnswerTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_learn_answer';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('ANSWER_ENTITY_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'QUESTION_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('ANSWER_ENTITY_QUESTION_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'SORT',
                [
                    'default' => 10,
                    'title' => Loc::getMessage('ANSWER_ENTITY_SORT_FIELD'),
                ]
            ),
            new TextField(
                'ANSWER',
                [
                    'required' => true,
                    'title' => Loc::getMessage('ANSWER_ENTITY_ANSWER_FIELD'),
                ]
            ),
            new StringField(
                'CORRECT',
                [
                    'required' => true,
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 1),
                        ];
                    },
                    'title' => Loc::getMessage('ANSWER_ENTITY_CORRECT_FIELD'),
                ]
            ),
            new TextField(
                'FEEDBACK',
                [
                    'title' => Loc::getMessage('ANSWER_ENTITY_FEEDBACK_FIELD'),
                ]
            ),
            new TextField(
                'MATCH_ANSWER',
                [
                    'title' => Loc::getMessage('ANSWER_ENTITY_MATCH_ANSWER_FIELD'),
                ]
            ),
        ];
    }
}
