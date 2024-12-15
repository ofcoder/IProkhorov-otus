<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
  /** @var array $arParams */
  /** @var array $arResult */
  /** @global \CMain $APPLICATION */
  /** @global \CUser $USER */
  /** @global \CDatabase $DB */
  /** @var \CBitrixComponentTemplate $this */
  /** @var string $templateName */
  /** @var string $templateFile */
  /** @var string $templateFolder */
  /** @var string $componentPath */
  /** @var array $templateData */
  /** @var \CBitrixComponent $component */
  $this->setFrameMode(true);
?>
<?php
  /**
   * Toolbar filter
   **/
  \Bitrix\Main\Loader::includeModule('ui');
  
  \Bitrix\UI\Toolbar\Facade\Toolbar::addFilter([
    'FILTER_ID' => $arResult['GRID_ID'],
    'GRID_ID' => $arResult['GRID_ID'],
    'FILTER' => $arResult['GRID_FILTER'],
    'ENABLE_LIVE_SEARCH' => true,
    'ENABLE_LABEL' => true
  ]);
  
  /**
   * Toolbar buttons
   * https://dev.1c-bitrix.ru/api_d7/bitrix/ui/toolbar/get_started.php
   **/
  $addButton = new \Bitrix\UI\Buttons\AddButton([
    "click" => new \Bitrix\UI\Buttons\JsCode(
      "alert('Кнопка нажата')"
    ),
    "text" => "Важная кнопка"
  ]);
  
  \Bitrix\UI\Toolbar\Facade\Toolbar::addButton($addButton);
?>
<?php $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
  'GRID_ID' => $arResult['GRID_ID'],
  'COLUMNS' => $arResult['GRID_COLUMNS'],
  'ROWS' => $arResult['ROWS'],
  'NAV_OBJECT' => $arResult['NAV'],
  'AJAX_MODE' => 'Y',
  'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
  'AJAX_OPTION_JUMP' => 'N',
  'SHOW_ROW_CHECKBOXES' => false,
  'SHOW_CHECK_ALL_CHECKBOXES' => false,
  'SHOW_ROW_ACTIONS_MENU' => true,
  'SHOW_GRID_SETTINGS_MENU' => true,
  'SHOW_NAVIGATION_PANEL' => true,
  'SHOW_PAGINATION' => true,
  'SHOW_SELECTED_COUNTER' => false,
  'SHOW_TOTAL_COUNTER' => false,
  'SHOW_PAGESIZE' => false,
  'SHOW_ACTION_PANEL' => false,
  'ALLOW_COLUMNS_SORT' => true,
  'ALLOW_COLUMNS_RESIZE' => true,
  'ALLOW_HORIZONTAL_SCROLL' => true,
  'ALLOW_SORT' => true,
  'ALLOW_PIN_HEADER' => true,
  'AJAX_OPTION_HISTORY' => 'N',
  "ENABLE_COLLAPSIBLE_ROWS" => true
], $component); ?>

<script>
  BX.ready(function() {
    BX.Ofcoder.BaseGrid.List.gridId = 'BASE_GRID'
    BX.Ofcoder.BaseGrid.List.pathTemplates = {
      userProfile: '/company/personal/user/#ID#/'
    }
  })
</script>
