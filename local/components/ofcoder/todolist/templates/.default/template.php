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
<section id="todo" class="todo">
  <h2>TodoList</h2>
  <form class="todo__filter form" id="todo__filter">
    <div class="form__field">
      <label for="">Поиск по тексту</label>
      <input type="text" name="%UF_TEXT">
    </div>
    <div class="form__buttons">
      <button class="form__button" type="submit">Найти</button>
    </div>
  </form>
  <ul class="todo__list" id="todo__list">
    <?php foreach ($arResult['TASKS'] as $key => $task){?>
      <li class="todo__item">
        <label>
          <input type="checkbox" <?=$task['UF_COMPLETED'] ? 'checked' : '' ?> data-id="<?=$task['ID']?>" />
          <?=$task['UF_TEXT']?>
        </label>
      </li>
    <?php }?>
  </ul>
  <form id="todo__add-form" class="form">
    <div class="form__field">
      <label for="">Новая задача</label>
      <textarea name="UF_TEXT"></textarea>
    </div>
    <div class="form__buttons">
      <button class="form__button" type="submit">Добавить</button>
      <button class="form__button" type="reset">Сбросить</button>
    </div>
  </form>
</section>

<script>
  // Передаем SIGNED_PARAMETERS в JS глобальный объкт BX.message
  BX.message({
    signedParameters: <?=$arResult['SIGNED_PARAMETERS']?>
  })
  //Рекомендую иницализировать скрипт компонента тут. Далее в статье объясню почему.
  new BX.TodoList()
</script>
