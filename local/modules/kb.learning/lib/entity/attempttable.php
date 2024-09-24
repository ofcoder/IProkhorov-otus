<?php
namespace Kb\Learning\Entity;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Entity\ReferenceField;

/**
 * Class AttemptTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TEST_ID int mandatory
 * <li> STUDENT_ID int mandatory
 * <li> DATE_START datetime mandatory
 * <li> DATE_END datetime optional
 * <li> STATUS string(1) optional default 'B'
 * <li> COMPLETED bool ('N', 'Y') optional default 'N'
 * <li> SCORE int optional default 0
 * <li> MAX_SCORE int optional default 0
 * <li> QUESTIONS int optional default 0
 * </ul>
 *
 * @package Bitrix\Learn
 **/

class AttemptTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_learn_attempt';
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
                    'title' => Loc::getMessage('ATTEMPT_ENTITY_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'TEST_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('ATTEMPT_ENTITY_TEST_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'STUDENT_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('ATTEMPT_ENTITY_STUDENT_ID_FIELD'),
                ]
            ),
            new DatetimeField(
                'DATE_START',
                [
                    'required' => true,
                    'title' => Loc::getMessage('ATTEMPT_ENTITY_DATE_START_FIELD'),
                ]
            ),
            new DatetimeField(
                'DATE_END',
                [
                    'title' => Loc::getMessage('ATTEMPT_ENTITY_DATE_END_FIELD'),
                ]
            ),
            new StringField(
                'STATUS',
                [
                    'default' => 'B',
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 1),
                        ];
                    },
                    'title' => Loc::getMessage('ATTEMPT_ENTITY_STATUS_FIELD'),
                ]
            ),
            new BooleanField(
                'COMPLETED',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                    'title' => Loc::getMessage('ATTEMPT_ENTITY_COMPLETED_FIELD'),
                ]
            ),
            new IntegerField(
                'SCORE',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('ATTEMPT_ENTITY_SCORE_FIELD'),
                ]
            ),
            new IntegerField(
                'MAX_SCORE',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('ATTEMPT_ENTITY_MAX_SCORE_FIELD'),
                ]
            ),
            new IntegerField(
                'QUESTIONS',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('ATTEMPT_ENTITY_QUESTIONS_FIELD'),
                ]
            ),
            new ReferenceField(
                'TEST',
                'Kb\Learning\Entity\Test',
                ['=this.TEST_ID' => 'ref.ID'],
                ['join_type' => 'LEFT']
            ),
            new ReferenceField(
                'STUDENT',
                'Bitrix\Main\User',
                ['=this.STUDENT_ID' => 'ref.ID'],
                ['join_type' => 'LEFT']
            ),
        ];
    }
}