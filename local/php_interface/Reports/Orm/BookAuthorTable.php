<?php
namespace Models\Orm;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
class BookAuthorTable extends DataManager
{
    public static function getTableName()
    {
        return 'o_book_author';
    }
    public static function getMap()
    {
        return array(
            (new IntegerField('BOOK_ID'))->configurePrimary(),
            new Reference(
                'BOOK',
                'Models\Orm\BookTable',
                array('=this.BOOK_ID' => 'ref.ID')
            ),
            (new IntegerField('AUTHOR_ID'))->configurePrimary(),
            new Reference(
                'AUTHOR',
                'Models\Orm\AuthorTable',
                array('=this.AUTHOR_ID' => 'ref.ID'),
            )
        );
    }
}
