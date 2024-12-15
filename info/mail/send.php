<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Mail\Event;


/***   Получаем данные из формы отправленные скриптом ***/
// перед присвоением в переменную, проверяем есть ли данные
if (!empty($_POST["name"])) $name = $_POST['name'];
if (!empty($_POST["email"])) $email = $_POST['email'];
if (!empty($_POST["text"])) $text = $_POST['text'];

/***   Проверка данных ***/
// валидация почты
$OK = false;
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
   $OK = true;
} else {
   $OK = false;
   $result['email'] = 'Неверный адрес электронной почты';
}

/***   Отправка данных ***/
if ($OK) {
   $OK = false;
   /** сохранение данных в инфоблок **/
   // подключаем модуль инфоблоков
   CModule::IncludeModule('iblock');
   // инициализируем
   $elem = new CIBlockElement;
   // создаем пустой массив и собираем в него поля
   $PROP = array();
   $PROP['NAME'] = $name;
   $PROP['EMAIL'] = $email;
   $PROP['TEXT'] = $text;

   $arF = array();   // пустой массив для сбора ид файлов
   $count = count($_FILES['file']['name']);  // количество файлов
   // соханяем файлы и получаем ид
   for ($i = 0; $i < $count; $i++) {
      $arIMAGE["name"] = $_FILES['file']['name'][$i];
      $arIMAGE["size"] = $_FILES['file']['size'][$i];
      $arIMAGE["tmp_name"] = $_FILES['file']['tmp_name'][$i];
      $arIMAGE["type"] = $_FILES['file']['type'][$i];
      $arIMAGE["MODULE_ID"] = "vote";
      $fid = CFile::SaveFile($arIMAGE, "vote");
      $arF[] = $fid;
   }
   // все ид файлов присваиваем свойству
   $PROP['FILES'] = $arF;

   // настройки
   $arLoadProductArray = array(
      "MODIFIED_BY" => 1,
      "IBLOCK_SECTION_ID" => false,    // элемент лежит в корне раздела
      "IBLOCK_ID" => 8,           // Ид инфоблока
      "PROPERTY_VALUES" => $PROP,       // массив со свойствами
      "NAME" => $email,      // имя записи
      "ACTIVE" => "Y",
   );
   // сохраняем
   $PRODUCT_ID = $elem->Add($arLoadProductArray);


   /*** отправка средствами Битрикс (((!!!старое ядро!!!))) ***/
   /* метод отправки    (SEND - тип почтового события,
                        s1 - ид сайта,
                        в массиве поля на отправку,
                        2 пустых необязательных параметра,
                        в конце массив ид файлов) */
//   CEvent::Send("SEND", 's1', array(
//      "AUTHOR" => $name,
//      "AUTHOR_EMAIL" => $email,
//      "TEXT" => $text,
//   ), '','', $arF);
//   unset($name);  // обнуление объекта

   /*** отправка средствами Битрикс методом Event::send из D7 ***/
   /* метод отправки    (SEND - тип почтового события,
                        s1 - ид сайта,
                        C_FIELDS поля на отправку,
                        2 пустых необязательных параметра,
                        FILE массив ид файлов) */
   Event::send(array(
      "EVENT_NAME" => "SEND",
      "LID" => "s1",
      "C_FIELDS" => array(
         "AUTHOR" => $name,
         "AUTHOR_EMAIL" => $email,
         "TEXT" => $text,
      ),
      "FILE" => $arF,
   ));


   // если отправка успешна
   $result['error'] = "";
   $result['success'] = 'Сообщение отправлено';
} else {
   $result['error'] = 'Сообщение не отправлено';
}

/***   Возврат результата отправки ***/
header('Content-Type: application/json');
echo json_encode($result);