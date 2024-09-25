<?php

namespace Kb\Learning\EventHandlers;

use Bitrix\Main\Context;

class Main
{
    public static function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu): void
    {
        foreach ($aModuleMenu as &$moduleMenu) {
            if ($moduleMenu['parent_menu'] == 'global_menu_services'
                && $moduleMenu['section'] == 'learning'
            ) {
                $aMenuLogs = [
                    'text'      => 'Журнал изменений курсов',
                    'url'       => 'kb_learn_logs.php?lang=ru',
                    'title'     => 'Тест kb',
                    'items_id'  => 'kb_learn_logs',
                    'icon'      => 'main_menu_icon',
                    'page_icon' => 'main_page_icon',
                    'more_url'  => [],
                ];
                $aMenuTestResult = [
                    'text'      => 'Результаты тестирования',
                    'url'       => 'kb_learn_test_result.php?lang=ru',
                    'title'     => 'Результаты тестировнаия',
                    'items_id'  => 'kb_learn_test_result',
                    'icon'      => 'learning_icon_gradebook',
                    'page_icon' => 'learning_icon_gradebook',
                    'more_url'  => [],
                ];
                $aMenuTest = [
                    'text'      => 'Результаты опроса',
                    'url'       => 'kb_learn_vote_result.php?lang=ru',
                    'title'     => 'Результаты опроса',
                    'items_id'  => 'kb_learn_vote_result',
                    'icon'      => 'vote_menu_icon',
                    'page_icon' => 'vote_page_icon',
                    'more_url'  => [],
                ];

                $moduleMenu['items'][] = $aMenuTest;
                unset($aMenuTest);
                $moduleMenu['items'][] = $aMenuTestResult;
                unset($aMenuTestResult);
                $moduleMenu['items'][] = $aMenuLogs;
                unset($aMenuLogs);

                break;
            }
        }
    }

    public static function OnEpilog(){
        $request = Context::getCurrent()->getRequest();
        $courseId = $request['COURSE_ID'];
        if ($request->getRequestedPage() == '/bitrix/admin/learn_course_edit.php' &&  $courseId > 0) {
            $asset = \Bitrix\Main\Page\Asset::getInstance();
            $asset->addJs('/local/modules/kb.learning/include/js/coursePopup.js');
        }
    }
}