<?php
namespace Models\Book;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\DateField;

class BookTable extends DataManager
{
    public static function getTableName()
    {
        return 'o_books';
    }

    public static function getMap()
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

            (new StringField('TITLE'))
                ->configureRequired(),

            (new IntegerField('YEAR')),

            (new IntegerField('COPIES_CNT')),

            (new IntegerField('PUBLISHING_ID')),

            (new Reference(
                'PUBLISHING',
                'Models\Book\PublishingTable',
                [
                    '=this.PUBLISHING_ID' => 'ref.ID'
                ],
            )),

            (new DateField('SALE_DATE_END')),
        ];
    }
}

