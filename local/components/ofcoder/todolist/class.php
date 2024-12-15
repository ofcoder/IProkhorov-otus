<?php
/**
 * Проверка на пролог
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
  die();
}

/**
 * Подключение зависимостей
 */

use Bitrix\Main\Loader,
  Bitrix\Main\Entity,
  Bitrix\Main\Web\Json,
  Bitrix\Main\UI\Extension,
  Bitrix\Highloadblock\HighloadBlockTable,
  Bitrix\Main\Engine\Contract\Controllerable;

/* Подключаю нотификации */
Extension::load('ofcoder.notification');

/**
 * Подключения модуля по работе с хайлоад блоками
 */
Loader::includeModule("highloadblock");

/** Создание своего кастомного ООП класса с наследованием от CBitrixComponent
 * и реализацией интерфейса Controllerable
 * для работы с AJAX запросами от JS библиотеки Bitrix
 */
class TodoList extends CBitrixComponent implements Controllerable
{
  public function onPrepareComponentParams($params)
  {
    return $params;
  }
  /**
   * Получение параметров компонента которые будут учтены при обращении к экшену компонента
   * через AJAX для этого надо будет при вызове этого action
   * передать signedParameters в параметры BX.ajax.runComponentAction;
   * @return array
   */
  public function listKeysSignedParameters(): array
  {
    return [
      'HIGHLOAD_BLOCK',
      'ADDED_TASK_NOTIFICATION_TEXT'
    ];
  }

  /** Настройка экшенов которые к которым будем обращаться через AJAX ядра
   * @return array
   */
  public function configureActions(): array
  {
    /**Отключаем у наших экшенов требования к авторизации пользователя на сайте */
    return [
      'getFilteredTasks' => [
        '-prefilters' => [
          ActionFilter\Authentication::class,
        ],
      ],
      'addTask' => [
        '-prefilters' => [
          ActionFilter\Authentication::class,
        ],
      ],
    ];
  }

  /**Выполнение компонента и подключение шаблона */
  public function executeComponent(): void
  {
    $this->getArResult();
    $this->includeComponentTemplate();
  }

  /** Получение ArResult */
  protected function getArResult(): void
  {
    /*Получаем todo задачи */
    $arResult['TASKS'] = $this->getTasks();

    /**Параметры которые мы передадим в JS
     * чтобы когда мы обращались к нашему экшену через AJAX,
     * у нашего компонента были arParams, и он мог выполнить логику*/
    $arResult['SIGNED_PARAMETERS'] = Json::encode($this->getSignedParameters());

    $this->arResult = $arResult;
  }

  /** Получение todo задач по фильтру, если входных параметров нет используем фильтр по умолчанию
   * @return array
   */
  protected function getTasks(array $filter = []): array
  {
    return HighloadBlockTable::compileEntity($this->arParams['HIGHLOAD_BLOCK'])->getDataClass()::getList([
      'select' => ['*'],
      'order' => ['ID' => 'ASC'],
      'filter' => $filter,
    ])->fetchAll();
  }


  /**Фильтруем пост */
  protected function filterPost(): void
  {
    /**Удаляем из поста параметры потому что они нам больше не нужны,
     * и будут мешать когда мы будем передавать в POST в filter */
    unset($_POST['signedParameters']);

    $_POST = array_filter($_POST, fn($value) => !empty(trim($value)));
  }

  /**Экшен по получению отфильтрованных задач */
  public function getFilteredTasksAction(): array
  {
    $this->filterPost();
    return $this->getTasks($_POST);
  }

  /**Экшен изменения статуса у задания */
  public function changeTaskStatusAction(int $taskId, string $taskCompleted): void
  {
    /**Обновляем статус */
    HighloadBlockTable::compileEntity($this->arParams['HIGHLOAD_BLOCK'])->getDataClass()
      ::update($taskId, ['UF_COMPLETED' => ($taskCompleted === "true")]);
  }

  /**Экшен добавления в базу */
  public function addTaskAction(): string
  {
    $this->filterPost();

    $addedTask = HighloadBlockTable::compileEntity($this->arParams['HIGHLOAD_BLOCK'])->getDataClass()::add($_POST);

    return $this->arParams['ADDED_TASK_NOTIFICATION_TEXT'];
  }
}