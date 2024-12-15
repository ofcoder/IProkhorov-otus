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
<!-- Подключаем API -->
<!-- Подробнее https://tech.yandex.ru/maps/doc/jsapi/2.1/dg/concepts/load-docpage/ -->
<div id="printMap" style="width: 700px; height: 700px;"></div>
<script src="https://api-maps.yandex.ru/2.1/?apikey=YOUR_APY_KEY&load=package.full&lang=ru-RU" type="text/javascript"></script>
<script>
ymaps.ready(init);

function init() {
var poligonMap = new ymaps.Map("printMap", {
center: [55.73, 37.75],
zoom: 10
}, {
searchControlProvider: 'yandex#search'
});

  // Строка с адресом, который необходимо геокодировать
  var address = 'Москва, ул. Льва Толстого, 16';

  // Ищем координаты указанного адреса
  // https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/geocode-docpage/
  var geocoder = ymaps.geocode(address);

  // После того, как поиск вернул результат, вызывается callback-функция
  geocoder.then(
    function (res) {

      // координаты объекта
      var coordinates = res.geoObjects.get(0).geometry.getCoordinates();

      // Добавление метки (Placemark) на карту
      var placemark = new ymaps.Placemark(
        coordinates, {
          'hintContent': address,
          'balloonContent': 'Время работы: Пн-Пт, с 9 до 20'
        }, {
          'preset': 'islands#redDotIcon'
        }
      );

      poligonMap.geoObjects.add(placemark);
    }
  );

// Создаем многоугольник, используя класс GeoObject.
var myGeoObject = new ymaps.GeoObject({
// Описываем геометрию геообъекта.
geometry: {
// Тип геометрии - "Многоугольник".
type: "Polygon",
// Указываем координаты вершин многоугольника.
coordinates: [
// Координаты вершин внешнего контура.
[
[55.75, 37.80],
[55.80, 37.90],
[55.75, 38.00],
[55.70, 38.00],
[55.70, 37.80]
],
// Координаты вершин внутреннего контура.
[
[55.75, 37.82],
[55.75, 37.98],
[55.65, 37.90]
]
],
// Задаем правило заливки внутренних контуров по алгоритму "nonZero".
fillRule: "nonZero"
},
// Описываем свойства геообъекта.
properties:{
// Содержимое балуна.
balloonContent: "Многоугольник"
}
}, {
// Описываем опции геообъекта.
// Цвет заливки.
fillColor: '#00FF00',
// Цвет обводки.
strokeColor: '#0000FF',
// Общая прозрачность (как для заливки, так и для обводки).
opacity: 0.5,
// Ширина обводки.
strokeWidth: 5,
// Стиль обводки.
strokeStyle: 'shortdash'
});

// Добавляем многоугольник на карту.
  poligonMap.geoObjects.add(myGeoObject);

//Добавление метки
  var shopPlacemark = new ymaps.Placemark([55.76, 37.64], {
    iconContent: '47',
    balloonContent: 'Столица России'
  }, {
    preset: 'twirl#blueStretchyIcon'
  });

  poligonMap.geoObjects.add(shopPlacemark);

// Создаем многоугольник, используя вспомогательный класс Polygon.
var myPolygon = new ymaps.Polygon([
// Указываем координаты вершин многоугольника.
// Координаты вершин внешнего контура.
[
[55.75, 37.50],
[55.80, 37.60],
[55.75, 37.70],
[55.70, 37.70],
[55.70, 37.50]
],
// Координаты вершин внутреннего контура.
[
[55.75, 37.52],
[55.75, 37.68],
[55.65, 37.60]
]
], {
// Описываем свойства геообъекта.
// Содержимое балуна.
hintContent: "Многоугольник"
}, {
// Задаем опции геообъекта.
// Цвет заливки.
fillColor: 'rgba(0,247,255,0.53)',
// Ширина обводки.
strokeWidth: 3
});

// Добавляем многоугольник на карту.
  poligonMap.geoObjects.add(myPolygon);
}
</script>
