<?php
namespace Models\Book;

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
                'Models\Book\BookTable',
                array('=this.BOOK_ID' => 'ref.ID')
            ),
            (new IntegerField('AUTHOR_ID'))->configurePrimary(),
            new Reference(
                'AUTHOR',
                'Models\Book\AuthorTable',
                array('=this.AUTHOR_ID' => 'ref.ID'),
            )
        );
    }
}
