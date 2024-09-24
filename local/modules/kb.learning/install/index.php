<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\Base;

use Kb\Learning\Entity\logTable;

Loc::loadMessages(__FILE__);

Class kb_learning extends \CModule
{
    const LEARN_EVENTS = [
        'OnAfterLessonAdd', // &$arFields
        'OnBeforeLessonDelete', // $id (Придется получить урок до удаления)
        'OnBeforeLessonUpdate', // &$arFields
        'OnAfterTestAdd', // $arFields
        'OnBeforeTestDelete', // $id (Придется получить тест до удаления)
        'OnBeforeTestUpdate', // $arFields, $id (Придется получить тест до обновления)
        'OnAfterQuestionAdd', // $id, $arFields
        'OnAfterQuestionDelete', // $id, $arQuestion даже после удаления передаются
        'OnAfterQuestionUpdate', // $id, $arFields (Знаем только поля после изменения, то что было - уже не узнать)
    ];

    public function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . '/version.php');
        $this->MODULE_ID = 'kb.learning';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('PARTNER_URI');

        $this->MODULE_GROUP_RIGHTS = 'Y';
    }

    public function doInstall()
    {
        try
        {
            Main\ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallFiles();
            $this->InstallDB();
            $this->InstallEvents();
        }
        catch (Exception $e)
        {
            global $APPLICATION;
            $APPLICATION->ThrowException($e->getMessage());

            return false;
        }

        return true;
    }

    public function doUninstall()
    {
        try
        {
            $this->UnInstallEvents();
            $this->unInstallDB();
            $this->UnInstallFiles();
            Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        }
        catch (Exception $e)
        {
            global $APPLICATION;
            $APPLICATION->ThrowException($e->getMessage());

            return false;
        }

        return true;
    }


    function InstallFiles($arParams = array())
    {
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/kb.learning/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', false);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/kb.learning/install/components', $_SERVER['DOCUMENT_ROOT'] . '/local/components', true, true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/kb.learning/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        DeleteDirFilesEx('/local/components/bitrix/learning.test/');

        return true;
    }

    function InstallDB()
    {
        if (!Loader::includeModule($this->MODULE_ID)) {
            return false;
        }

        if (!Application::getConnection()->isTableExists(LogTable::getTableName())) {
            LogTable::getEntity()->createDbTable();
        }

        $this->InstallTasks();

        $connection = Application::getInstance()->getConnection();

        if ($connection->isTableExists('b_learn_test_result')) {
            $dbName = $connection->getDbName();
            $sql = "
                SELECT 1 FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = '{$dbName}'
                AND TABLE_NAME = 'b_learn_test_result'
                AND COLUMN_NAME = 'date_insert'
                ";

            if (!$connection->query($sql)->fetch()) {
                $addFieldSql = "
                    ALTER TABLE b_learn_test_result
                    ADD DATE_INSERT datetime NULL
                    ";
                $connection->queryExecute($addFieldSql);
            }

            $fillEmptyDateSql = "
                    UPDATE b_learn_test_result as test_result
                    SET date_insert = (
	                    SELECT attempt.date_start
	                    FROM b_learn_attempt as attempt
	                    WHERE attempt.id = test_result.attempt_id
                    )
                    WHERE date_insert IS NULL;
                    ";

            $connection->queryExecute($fillEmptyDateSql);
        }

        return true;
    }

    function unInstallDB()
    {
        if (!Loader::includeModule($this->MODULE_ID)) {
            return false;
        }

        $this->UnInstallTasks();

        $connection = Application::getInstance()->getConnection();

        if (Application::getConnection()->isTableExists(LogTable::getTableName())) {
            $connection->dropTable(LogTable::getTableName());
        }

        return true;
    }


    function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler('main', 'OnBuildGlobalMenu', 'kb.learning', 'Kb\\Learning\\EventHandlers\\Main', 'OnBuildGlobalMenu');
        $eventManager->registerEventHandler('main', 'OnEpilog', 'kb.learning', 'Kb\\Learning\\EventHandlers\\Main', 'OnEpilog');

        foreach (self::LEARN_EVENTS as $eventName) {
            $eventManager->registerEventHandler('learning', $eventName, 'kb.learning', 'Kb\\Learning\\EventHandlers\\Learning', $eventName);
        }

        return true;
    }

    function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler('main', 'OnBuildGlobalMenu', 'kb.learning', 'Kb\\Learning\\EventHandlers\\Main', 'OnBuildGlobalMenu');
        $eventManager->unRegisterEventHandler('main', 'OnEpilog', 'kb.learning', 'Kb\\Learning\\EventHandlers\\Main', 'OnEpilog');

        foreach (self::LEARN_EVENTS as $eventName) {
            $eventManager->unRegisterEventHandler('learning', $eventName, 'kb.learning', 'Kb\\Learning\\EventHandlers\\Learning', $eventName);
        }

        return true;
    }

    public function GetModuleTasks()
    {
        return [
            'kb_learning_manager' => [
                "LETTER" => "W",
                "OPERATIONS" => [
                    "view_other_settings",
                ],
            ],
        ];
    }
}
?>