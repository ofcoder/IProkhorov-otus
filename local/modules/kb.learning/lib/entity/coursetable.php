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
 * Class CourseTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime optional default current datetime
 * <li> ACTIVE bool ('N', 'Y') optional default 'Y'
 * <li> CODE string(50) optional
 * <li> NAME string(255) optional default 'name'
 * <li> SORT int optional default 500
 * <li> PREVIEW_PICTURE int optional
 * <li> PREVIEW_TEXT text optional
 * <li> PREVIEW_TEXT_TYPE enum ('text', 'html') optional default 'text'
 * <li> DESCRIPTION text optional
 * <li> DESCRIPTION_TYPE enum ('text', 'html') optional default 'text'
 * <li> ACTIVE_FROM datetime optional
 * <li> ACTIVE_TO datetime optional
 * <li> RATING string(1) optional
 * <li> RATING_TYPE string(50) optional
 * <li> SCORM bool ('N', 'Y') optional default 'N'
 * <li> LINKED_LESSON_ID int optional
 * <li> JOURNAL_STATUS int optional default 0
 * </ul>
 *
 * @package Bitrix\Learn
 **/

class CourseTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_learn_course';
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
                    'title' => Loc::getMessage('COURSE_ENTITY_ID_FIELD'),
                ]
            ),
            new DatetimeField(
                'TIMESTAMP_X',
                [
                    'default' => function()
                    {
                        return new DateTime();
                    },
                    'title' => Loc::getMessage('COURSE_ENTITY_TIMESTAMP_X_FIELD'),
                ]
            ),
            new BooleanField(
                'ACTIVE',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                    'title' => Loc::getMessage('COURSE_ENTITY_ACTIVE_FIELD'),
                ]
            ),
            new StringField(
                'CODE',
                [
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 50),
                        ];
                    },
                    'title' => Loc::getMessage('COURSE_ENTITY_CODE_FIELD'),
                ]
            ),
            new StringField(
                'NAME',
                [
                    'default' => 'name',
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 255),
                        ];
                    },
                    'title' => Loc::getMessage('COURSE_ENTITY_NAME_FIELD'),
                ]
            ),
            new IntegerField(
                'SORT',
                [
                    'default' => 500,
                    'title' => Loc::getMessage('COURSE_ENTITY_SORT_FIELD'),
                ]
            ),
            new IntegerField(
                'PREVIEW_PICTURE',
                [
                    'title' => Loc::getMessage('COURSE_ENTITY_PREVIEW_PICTURE_FIELD'),
                ]
            ),
            new TextField(
                'PREVIEW_TEXT',
                [
                    'title' => Loc::getMessage('COURSE_ENTITY_PREVIEW_TEXT_FIELD'),
                ]
            ),
            new StringField(
                'PREVIEW_TEXT_TYPE',
                [
                    'values' => array('text', 'html'),
                    'default' => 'text',
                    'title' => Loc::getMessage('COURSE_ENTITY_PREVIEW_TEXT_TYPE_FIELD'),
                ]
            ),
            new TextField(
                'DESCRIPTION',
                [
                    'title' => Loc::getMessage('COURSE_ENTITY_DESCRIPTION_FIELD'),
                ]
            ),
            new StringField(
                'DESCRIPTION_TYPE',
                [
                    'values' => array('text', 'html'),
                    'default' => 'text',
                    'title' => Loc::getMessage('COURSE_ENTITY_DESCRIPTION_TYPE_FIELD'),
                ]
            ),
            new DatetimeField(
                'ACTIVE_FROM',
                [
                    'title' => Loc::getMessage('COURSE_ENTITY_ACTIVE_FROM_FIELD'),
                ]
            ),
            new DatetimeField(
                'ACTIVE_TO',
                [
                    'title' => Loc::getMessage('COURSE_ENTITY_ACTIVE_TO_FIELD'),
                ]
            ),
            new StringField(
                'RATING',
                [
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 1),
                        ];
                    },
                    'title' => Loc::getMessage('COURSE_ENTITY_RATING_FIELD'),
                ]
            ),
            new StringField(
                'RATING_TYPE',
                [
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 50),
                        ];
                    },
                    'title' => Loc::getMessage('COURSE_ENTITY_RATING_TYPE_FIELD'),
                ]
            ),
            new BooleanField(
                'SCORM',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                    'title' => Loc::getMessage('COURSE_ENTITY_SCORM_FIELD'),
                ]
            ),
            new IntegerField(
                'LINKED_LESSON_ID',
                [
                    'title' => Loc::getMessage('COURSE_ENTITY_LINKED_LESSON_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'JOURNAL_STATUS',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('COURSE_ENTITY_JOURNAL_STATUS_FIELD'),
                ]
            ),
        ];
    }
}
