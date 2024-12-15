<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
CJSCore::Init(array("popup"));

//для отладки vue
define('VUEJS_DEBUG', true);

//отключить локализации во Vue приложении (показывать коды)
//define('VUEJS_LOCALIZATION_DEBUG', true);

//подключение приложения doctors
\Bitrix\Main\UI\Extension::load('ofcoder.doctors');
\Bitrix\Main\UI\Extension::load('main.loader');
?>
<script>
  //import {Loader} from 'main.loader';

</script>

<div class="doctors">


<ul class="doctors__list">
  <?php foreach ($arResult['ITEMS'] as $doctor=>$pocedures){?>
      <li class="doctor__item">
        <?=$doctor?>
        <ul class="procedures_list">
            <?php foreach($pocedures as $pocedure){?>
              <li class="procedures__item">
                  <?=$pocedure?>
              </li>
            <?php }?>
        </ul>
      </li>
  <?php }?>
</ul>

<script>
  BX.Runtime.loadExtension('ofcoder.doctors');
</script>


    <div id="app">
        <h3>Value {{ count }}</h3>
        <counter :changefn="increase"></counter>
    </div>
    <script src="https://unpkg.com/vue"></script>
    <script>
      const app = Vue.createApp({
        data(){
          return { count: 0 }
        },
        methods:{
          increase(){
            this.count++;
          }
        }
      });

      app.component('counter', {
        props: ["changefn"],
        template: `<div><button v-on:click="changefn()">+</button></div>`
      });
      app.mount('#app');
    </script>