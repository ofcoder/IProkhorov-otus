<?php
  //if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
  
  namespace Ofcoder\Components;
  
  use Bitrix\Main\Grid;
  use Bitrix\Main\Grid\Panel\Types;
  use Bitrix\Main\UI;
  /*
   * https://aclips.ru/grid-component-bitrix24/
   *
public function executeComponent() — основной код компонента;
public function getEntityRepository() — возвращает объект сущности (репозиторий) для получения данных;
public function initNav($grid_options, $page_size) — получение объекта для постраничной навигации;
public function getSorting($grid) — получение сортировки;
public function getEntityFilter($grid_id, $grid_filter) — получение объекта для фильтрации;
public function getEntitySelect() — получение списка полей, которые будут участвовать в выборке;
public function getPreparedElement($fields) — метод для преобразования элементов
public function getElementActions($fields) — получение действий для выполнения над элементом;
private function getFilterFields($option = []) — получение полей фильтра;
private function getGridColumns($option = []) — получение полей списка;
private function prepareFilter($grid_id, $grid_filter) — преобразование данных, полученных из фильтра.
   * */
  class BaseGrid extends \CBitrixComponent
  {
    const GRID_ID = 'BASE_GRID';
    
    const PAGE_SIZE = 15;
    public function onPrepareComponentParams($params)
    {
      return $params;
    }
    
    public function executeComponent()
    {
      $grid_id = self::GRID_ID;
      $grid_options = new Grid\Options($grid_id);
      
      $grid_filter = $this->getFilterFields();
      
      $entityRepository = $this->getEntityRepository();
      
      $filter = $this->getEntityFilter($grid_id, $grid_filter);
      
      $select = $this->getEntitySelect();
      
      $sort = $this->getSorting($grid_options);
      
      $page_size = $this->arParams['PAGE_SIZE'] ?? self::PAGE_SIZE;
      
      //$nav = $this->initNav($grid_options, $page_size);
      $nav = $this->initNav($grid_options);
      
      $action_panel = $this->getActionPanel();
      
      
      $elements = $entityRepository::getList([
        'filter' => $filter,
        'select' => $select,
        "order" => $sort,
        "count_total" => true,
        "offset" => $nav->getOffset(),
        "limit" => $nav->getLimit()
      ]);
      
      $nav->setRecordCount($elements->getCount());
      
      $grid_rows = [];
      
      foreach ($elements as $element) {
        $prepared_element = $this->getPreparedElement($element);
        
        $actions = $this->getElementActions($element);
        
        $row = [
          'id' => $element['ID'],
          'data' => $element,
          'columns' => $prepared_element,
          'editable' => 'Y',
          'actions' => $actions
        ];
        
        $grid_rows[] = $row;
      }
      
      $this->arResult['NAV'] = $nav;
      
      $this->arResult['GRID_ID'] = $grid_id;
      $this->arResult['GRID_FILTER'] = $grid_filter;
      $this->arResult['GRID_COLUMNS'] = $this->getGridColumns();
      $this->arResult['ROWS'] = $grid_rows;
      $this->arResult["ACTION_PANEL"] = $action_panel;
      
      $this->includeComponentTemplate();
    }
    
     /*
     * Используется ORM \Bitrix\Main\ORM\Data\DataManager
     * Возвращает объект, отдающий данные.
     * */
    public function getEntityRepository()
    {
      //Пользователи
      $entityReporitory = new \Bitrix\Main\UserTable();
      
      return $entityReporitory;
    }
    
    public function initNav($grid_options)
    {
      $navParams = $grid_options->GetNavParams();
      
      $grid_id = $grid_options->getid();
      
      $nav = new UI\PageNavigation($grid_id);
      
      $pageSizes = [];
      foreach (["5", "10", "20", "30", "50", "100"] as $index) {
        $pageSizes[] = ['NAME' => $index, 'VALUE' => $index];
      }
      
      $nav->allowAllRecords(true)
        ->setPageSize($navParams['nPageSize'])
        ->setPageSizes($pageSizes)
        ->initFromUri();
      
      return $nav;
    }
    
    public function getSorting($grid)
    {
      $sort = $grid->GetSorting([
        'sort' => [
          'ID' => 'DESC'
        ],
        'vars' => [
          'by' => 'by',
          'order' => 'order'
        ]
      ]);
      
      return $sort['sort'];
    }
    
    public function getEntityFilter($grid_id, $grid_filter)
    {
      return $this->prepareFilter($grid_id, $grid_filter);
    }
    
    public function getEntitySelect()
    {
      return ['*'];
    }
    
    
    /*
     * каждый элемент попадает в данный метод
     * поле ACTIVE со значениями Y и N, а в списке
     * мы хотим отображать словами «Пользователь активен» или «Пользователь не активен»
     * */
    public function getPreparedElement($fields)
    {
      $fields['ACTIVE'] = $fields['ACTIVE'] == 'Y' ? 'Пользователь активен' : 'Пользователь не активен';
      
      return $fields;
    }
    
    /*
     * метод возвращает массив действий над записями. Каждый элемент действий содержит 3 свойства:

text — название действия, отображаемое в контекстном меню;
onclick — js действие, выполняемое при выборе пункта;
default — флаг, отвечающий за действие, выполняемое при двойном клике по записи в списке.
     * */
    public function getElementActions($fields)
    {
      $actions = [];
      
      $actions[] = [
        'text' => 'Открыть',
        'onclick' => "BX.Ofcoder.BaseGrid.List.openProfile({$fields['ID']})",
        'default' => true
      ];
      
      $actions[] = [
        'text' => 'Удалить',
        'onclick' => "BX.Ofcoder.BaseGrid.List.showRemoveUserConfirmation({$fields['ID']})",
      ];
      
      $actions[] = [
        'text' => 'Base Action',
        'onclick' => "BX.Ofcoder.Base.List.baseAction('${fields['NAME']}')",
        'default' => true
      ];
      
      return $actions;
    }
    
    /*
     *Возвращает массив элементов, которые должны отображаться в фильтре. Состоят элементы из:

id — Идентификатор поля фильтра;
name — Отображаемое название;
type — Тип поля (влияет на способ отображения);
default — флаг, отвечающий за отображение поля в фильтре по умолчанию.
У каждого типа могут быть свои дополнительные параметры.
     * */
    private function getFilterFields(): array
    {
      $filterFields = [
        [
          'id' => 'NAME',
          'name' => 'Имя',
          'type' => 'string',
          'default' => true
        ],
        [
          'id' => 'DATE_REGISTER',
          'name' => 'Дата регистрации',
          'type' => 'date',
          'default' => true
        ],
        [
          'id' => 'USER',
          'name' => "Пользователь",
          'type' => 'dest_selector',
          'default' => true,
        ],
        [
          'id' => 'UF_DEPARTMENT',
          'name' => 'Подразделение',
          'type' => 'entity_selector',
          'params' => [
            'multiple' => 'Y',
            'dialogOptions' => [
              'height' => 240,
              'context' => 'filter',
              'entities' => [
                [
                  'id' => 'department',
                  'options' => [
                    "selectMode" => "departmentsOnly",
                    "allowFlatDepartments" => true,
                  ],
                ],
              ]
            ],
          ],
          'default' => true,
        ],
        [
          'id' => 'ACTIVE',
          'name' => 'Активность',
          'type' => 'list',
          'items' => ["" => "Не указана", "Y" => "Да", "N" => "Нет"],
          'default' => true
        ],
      ];
      
      return $filterFields;
    }

    
    /*
     * Массив отображаемых полей, содержащих:

id — Идентификатор поля;
name — Отображаемое название в списке;
sort — Идентификатор поля, по которому будет проводиться сортировка;
default — Флаг, отвечающий за отображение в списке по умолчанию.
     * */
    
    private function getGridColumns()
    {
      $columns = [
        [
          'id' => 'NAME',
          'name' => 'Имя',
          'sort' => 'NAME',
          'default' => true
        ],
        [
          'id' => 'ACTIVE',
          'name' => 'Активность',
          'sort' => 'ACTIVE',
          'default' => true
        ],
        [
          'id' => 'DATE_REGISTER',
          'name' => 'Дата регистрации',
          'sort' => 'DATE_REGISTER',
          'default' => true
        ]
      ];
      
      return $columns;
    }
    
    protected function getActionPanel(): array
    {
      $panel = [
        'GROUPS' => [
          [
            'ITEMS' => [
              [
                'TYPE' => Types::BUTTON,
                'ID' => "group_action_button",
                'CLASS' => "apply",
                'TEXT' => "Base Group Action",
                'ONCHANGE' => [[
                  'ACTION' => 'CALLBACK',
                  'DATA' => [
                    ['JS' => 'BX.Ofcoder.Base.List.baseGroupAction()'],
                  ],
                ],
                ],
              ],
            ],
          ],
        ],
      ];
      
      return $panel;
    }
    
    
    /*
     *Формирует из полученных данных массив для фильтрации элементов.
     * */
    private function prepareFilter($grid_id, $grid_filter): array
    {
      $filter = [];
      
      $filterOption = new \Bitrix\Main\UI\Filter\Options($grid_id);
      $filterData = $filterOption->getFilter([]);
      
      foreach ($filterData as $k => $v) {
        $filter[$k] = $v;
      }
      
      $filterPrepared = \Bitrix\Main\UI\Filter\Type::getLogicFilter($filter, $grid_filter);
      
      if (!empty($filter['FIND'])) {
        $findFilter = [
          'LOGIC' => 'OR',
          [
            '%NAME' => $filter['FIND']
          ]
        ];
        
        if (!empty($filterPrepared)) {
          $filterPrepared[] = $findFilter;
        } else {
          $filterPrepared = $findFilter;
        }
      }
      
      if (!empty($filterPrepared['USER'])) {
        $filterPrepared["ID"] = str_replace("U", "", $filterData['USER']);
        unset($filterPrepared["USER"]);
      }
      
      
      return $filterPrepared;
    }
    
 
    
  }