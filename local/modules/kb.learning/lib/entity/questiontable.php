<?php
namespace Kb\Learning\Entity;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

/**
 * Class QuestionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ACTIVE bool ('N', 'Y') optional default 'Y'
 * <li> TIMESTAMP_X datetime optional default current datetime
 * <li> LESSON_ID int mandatory
 * <li> QUESTION_TYPE string(1) optional default 'S'
 * <li> NAME string(255) mandatory
 * <li> SORT int optional default 500
 * <li> DESCRIPTION text optional
 * <li> DESCRIPTION_TYPE enum ('text', 'html') optional default 'text'
 * <li> COMMENT_TEXT text optional
 * <li> FILE_ID int optional
 * <li> SELF bool ('N', 'Y') optional default 'N'
 * <li> POINT int optional default 10
 * <li> DIRECTION string(1) optional default 'V'
 * <li> CORRECT_REQUIRED bool ('N', 'Y') optional default 'N'
 * <li> EMAIL_ANSWER bool ('N', 'Y') optional default 'N'
 * <li> INCORRECT_MESSAGE text optional
 * </ul>
 *
 * @package Kb\Learning\Entity
 **/

class QuestionTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_learn_question';
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
                    'title' => Loc::getMessage('QUESTION_ENTITY_ID_FIELD'),
                ]
            ),
            new BooleanField(
                'ACTIVE',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                    'title' => Loc::getMessage('QUESTION_ENTITY_ACTIVE_FIELD'),
                ]
            ),
            new DatetimeField(
                'TIMESTAMP_X',
                [
                    'default' => function()
                    {
                        return new DateTime();
                    },
                    'title' => Loc::getMessage('QUESTION_ENTITY_TIMESTAMP_X_FIELD'),
                ]
            ),
            new IntegerField(
                'LESSON_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('QUESTION_ENTITY_LESSON_ID_FIELD'),
                ]
            ),
            new StringField(
                'QUESTION_TYPE',
                [
                    'default' => 'S',
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 1),
                        ];
                    },
                    'title' => Loc::getMessage('QUESTION_ENTITY_QUESTION_TYPE_FIELD'),
                ]
            ),
            new StringField(
                'NAME',
                [
                    'required' => true,
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 255),
                        ];
                    },
                    'title' => Loc::getMessage('QUESTION_ENTITY_NAME_FIELD'),
                ]
            ),
            new IntegerField(
                'SORT',
                [
                    'default' => 500,
                    'title' => Loc::getMessage('QUESTION_ENTITY_SORT_FIELD'),
                ]
            ),
            new TextField(
                'DESCRIPTION',
                [
                    'title' => Loc::getMessage('QUESTION_ENTITY_DESCRIPTION_FIELD'),
                ]
            ),
            new StringField(
                'DESCRIPTION_TYPE',
                [
                    'values' => array('text', 'html'),
                    'default' => 'text',
                    'title' => Loc::getMessage('QUESTION_ENTITY_DESCRIPTION_TYPE_FIELD'),
                ]
            ),
            new TextField(
                'COMMENT_TEXT',
                [
                    'title' => Loc::getMessage('QUESTION_ENTITY_COMMENT_TEXT_FIELD'),
                ]
            ),
            new IntegerField(
                'FILE_ID',
                [
                    'title' => Loc::getMessage('QUESTION_ENTITY_FILE_ID_FIELD'),
                ]
            ),
            new BooleanField(
                'SELF',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                    'title' => Loc::getMessage('QUESTION_ENTITY_SELF_FIELD'),
                ]
            ),
            new IntegerField(
                'POINT',
                [
                    'default' => 10,
                    'title' => Loc::getMessage('QUESTION_ENTITY_POINT_FIELD'),
                ]
            ),
            new StringField(
                'DIRECTION',
                [
                    'default' => 'V',
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 1),
                        ];
                    },
                    'title' => Loc::getMessage('QUESTION_ENTITY_DIRECTION_FIELD'),
                ]
            ),
            new BooleanField(
                'CORRECT_REQUIRED',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                    'title' => Loc::getMessage('QUESTION_ENTITY_CORRECT_REQUIRED_FIELD'),
                ]
            ),
            new BooleanField(
                'EMAIL_ANSWER',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                    'title' => Loc::getMessage('QUESTION_ENTITY_EMAIL_ANSWER_FIELD'),
                ]
            ),
            new TextField(
                'INCORRECT_MESSAGE',
                [
                    'title' => Loc::getMessage('QUESTION_ENTITY_INCORRECT_MESSAGE_FIELD'),
                ]
            ),
        ];
    }
}
