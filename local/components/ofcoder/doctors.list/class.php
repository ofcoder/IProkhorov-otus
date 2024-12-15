<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable,
    Bitrix\Main\UI\PageNavigation,
    Bitrix\Main\Engine\CurrentUser,
    Bitrix\Main\Entity\Query,
    Bitrix\Main\Entity\ExpressionField,
    Bitrix\Main\Entity\ReferenceField,
    Bitrix\Main\Loader,
    Bitrix\Main\Application,
    Bitrix\Iblock\Elements\ElementDoctorsTable  as DoctorsTable,
    Bitrix\Iblock\Elements\ElementProceduresTable  as ProceduresTable,
    Ofcoder\Diag\Helper
    ;
/*
use Models\Lists\DoctorsPropertyValuesTable as Doctors,
    Models\Lists\ProceduresPropertyValuesTable as Procedures;
*/
class Doctors extends \CBitrixComponent implements Controllerable
{
  private const LOG_PATH = '/home/c/ct93339/public_html/local/logs/';

  private $defaultSelect = [
      'ID',
      'NAME',
      'UF_SURNAME',
      'UF_NAME',
      'UF_PATRONYMIC',
      'UF_PROCEDURE_ID.ELEMENT.NAME'
    ];
  private $defaultFilter = ['ACTIVE' => 'Y'];
  private $defaultOrder = ['ID' => 'ASC'];
	public function configureActions()
    {
        return [
          'getDoctors' => [
            'prefilters' => [
              new ActionFilter\Authentication(),
            ],
          ],
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        $result = [
            'CACHE_TIME' => isset($arParams['CACHE_TIME']) ? $arParams['CACHE_TIME'] : 3600,
            'CACHE_TYPE' => isset($arParams['CACHE_TYPE']) ? $arParams['CACHE_TYPE'] : 'A',
        ];
        return $result;
    }
	
	public function getDoctorsAction( $select = [], $filter = [], $order = [] )
  {
    if(count($select) == 0)
      $select = $this->defaultSelect;
    if(count($filter) == 0)
      $filter = $this->defaultFilter;
    if(count($order) == 0)
      $order = $this->defaultOrder;
    return $this->getDoctors( $select, $filter, $order);
  }
	private function getProcedures(){
    $query = new Query(Bitrix\Iblock\Elements\ElementProceduresTable::getEntity());

    $query->setFilter([
      'ACTIVE' => 'Y',
    ]);
    $query->setSelect(['*']);
    $result = $query->exec();
    $resultAr =  $result->fetchAll();

    $procedures = [];
    foreach($resultAr as $row) {
      $procedures[$row['ID']] =  $row['NAME'];
    }

    Helper::log2file($query->getQuery(), 'getQuery_getProcedures', self::LOG_PATH);
    Helper::log2file($procedures, 'getQuery_getProcedures', self::LOG_PATH);
    return $procedures;
  }
	private function getDoctors($select=[], $filter=[], $order=[])
  {
    $query = new Query(Bitrix\Iblock\Elements\ElementDoctorsTable::getEntity());

    $query->setFilter([
      'ACTIVE' => 'Y',
    ]);
    $query->setSelect([
      'ID',
      'CODE',
      'PROCEDURE' => 'UF_PROCEDURE_ID.ELEMENT.NAME',
      new ExpressionField('FULL_NAME', 'CONCAT(%s," ",%s," ",%s)', ['UF_SURNAME.VALUE', 'UF_NAME.VALUE', 'UF_PATRONYMIC.VALUE']),
      //new ExpressionField('DOCTOR_PROCEDURES', 'GROUP_CONCAT(%s)', 'FULL_NAME'),
    ]);

    $result = $query->exec();
    $resultAr =  $result->fetchAll();

    $doctors = [];
    foreach($resultAr as $row) {
      $doctors[$row['FULL_NAME']][] =  $row['PROCEDURE'];
    }

    Helper::log2file($query->getQuery(), 'getQuery_getDoctors', self::LOG_PATH);
    Helper::log2file($doctors, 'doctors_getDoctors', self::LOG_PATH);
    Helper::log2file($resultAr, '$resultAr_getDoctors', self::LOG_PATH);
    return $doctors;

    /*
      $query->registerRuntimeField(new ReferenceField( //Подзапрос в другую таблицу
          "PROCEDURS",
          "Bitrix\Iblock\Elements\ElementProceduresTable",
          ["=this.UF_PROCEDURE_ID" => "ref.ID"]
      ));
      */
    //log2file($query->getQuery(), 'getQuery_getDoctors', self::LOG_PATH);
    // Debug::dumpToFile($shop['UF_STATUS'], 'UF_STATUS', '/local/logs/class-'.date("Y-m-d").'.txt')
    /*
      $doctors = \Bitrix\Iblock\Elements\ElementDoctorsTable::query()
        ->setSelect($select)
        ->setFilter($filter)
        ->setOrder($order)
        ->fetchCollection();
      $doctorsAr = [];
      foreach ($doctors as $doctor){
        $doctorName = $doctor->getUfSurname()->getValue() . ' ' . $doctor->getUfName()->getValue() . ' ' . $doctor->getUfPatronymic()->getValue();
        $doctorProcedures = [];
        foreach($doctor->getUfProcedureId()->getAll() as $prItem) {
          //var_dump($prItem->getId().' - '.$prItem->getElement()->getName());
          $doctorProcedures[] = [
            'name'=> $prItem->getElement()->getName(),
            'id' => $prItem->getElement()->getId()
          ];
        }
         $doctorsAr[$doctorName]['PROCEDURES'] = $doctorProcedures;
      }
    */
  }
  public function updateDoctorsAction($id, $name = '')
  {
    $fields['UF_NAME'] = $name;
    return $this->UpdateDoctors($id, $fields);
  }
	private function updateDoctors($id, $fields = [])
  {
    return DoctorsTable::update($id, $fields)->isSuccess();
  }
  public function addDoctorsAction($fields = [])
  {
    return $this->addtDoctors($fields);
  }
	private function addtDoctors($fields)
  {
    return DoctorsTable::add($fields)->isSuccess();
  }
  public function deleteDoctorsAction($id = 0)
  {
    return $this->deleteDoctors($id);
  }
	private function deleteDoctors($id)
  {
    return DoctorsTable::delete($id)->isSuccess();
  }
	public function executeComponent()
    {
        $cache = \Bitrix\Main\Data\Cache::createInstance();

        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

        if ($cache->initCache($this->arParams['CACHE_TIME'], 'Doctors' . CurrentUser::get()->getId())) {
            $this->arResult = $cache->getVars();
        } else {
   
            $this->arResult['ITEMS'] = $this->getDoctors();
            $this->arResult['PROCEDURES'] = $this->getProcedures();

            $cache->endDataCache($this->arResult);

            $this->includeComponentTemplate();

        }
    }
}


/**************************/
/*
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\ReferenceField;

$res = \Bitrix\Iblock\Elements\ElementDoctorsTable::getList([
   'runtime' => [
                new ExpressionField('DOCTOR', 'CONCAT(%s," ",%s," ",%s)', ['UF_SURNAME.VALUE', 'UF_NAME.VALUE', 'UF_PATRONYMIC.VALUE']),

            ],
    'select' => [
				'NAME',
				'DOCTOR',
				'PROCEDURE' => 'UF_PROCEDURE_ID.ELEMENT.NAME',
				],

    'filter' => ['=ACTIVE' => 'Y'],
])->fetchAll();



$doctorsProcedure = [];
foreach($res as $doc){
	$doctorsProcedure[$doc['DOCTOR']][] = $doc['PROCEDURE'];
}
print_r($doctorsProcedure);

 * */

/*
 *
use Bitrix\Main\Entity\ExpressionField;

$doctors = \Bitrix\Iblock\Elements\ElementDoctorsTable::query()
->setSelect([
    'ID',
    'NAME',
    'UF_PROCEDURE_ID.ELEMENT.NAME',
    new ExpressionField("FULL_NAME", 'CONCAT(%s," ",%s," ",%s)', ['UF_SURNAME.VALUE', 'UF_NAME.VALUE', 'UF_PATRONYMIC.VALUE'])
])
->setFilter(array('=ACTIVE' => 'Y'))
//;var_dump($doctors->getQuery());

->fetchCollection();

// затем обходим коллекцию и получаем процедуры
$procedures = [];
foreach ($doctors as $doctor){
    var_dump($doctor->get('NAME'));
var_dump($doctor->get("FULL_NAME"));


    foreach($doctor->getUfProcedureId()->getAll() as $prItem) {
        var_dump($prItem->getId().' - '.$prItem->getElement()->getName());
        $procedures[$doctor->get('NAME')] = [
            'name'=> $prItem->getElement()->getName(),
            'id' => $prItem->getElement()->getId()
        ];
    }

}
 * */