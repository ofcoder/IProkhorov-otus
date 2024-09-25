<?php

namespace Kb\Learning\Entity;

use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;

class LogTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'kb_learn_log';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', [
                    'primary' => true,
                    'autocomplete' => true
                ]),
            new Entity\IntegerField('USER_ID'),
            new Entity\StringField('DESCRIPTION'),
            new Entity\DateTimeField('LOG_DATE', ['default_value' => new DateTime])
        );
    }
}