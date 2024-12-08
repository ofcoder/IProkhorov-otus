<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable,
    Bitrix\Main\UI\PageNavigation,
    Bitrix\Main\Engine\CurrentUser,
    Bitrix\Main\Entity\Query,
    Bitrix\Main\Entity\ExpressionField,
    Bitrix\Main\Entity\ReferenceField,
    Bitrix\Main\Loader,
    Bitrix\Iblock\Elements\ElementDoctorsTable  as DoctorsTable,
    Bitrix\Iblock\Elements\ElementProceduresTable  as ProceduresTable;
/*
use Models\Lists\DoctorsPropertyValuesTable as Doctors,
    Models\Lists\ProceduresPropertyValuesTable as Procedures;
*/
class Doctors extends \CBitrixComponent implements Controllerable
{
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
            'CACHE_TIME' => isset($arParams['CACHE_TIME']) ? $arParams['CACHE_TIME'] : 36000000,
            'CACHE_TYPE' => isset($arParams['CACHE_TYPE']) ? $arParams['CACHE_TYPE'] : 'A',
        ];
        return $result;
    }
	
	public function getDoctorsAction()
  {
    $select = ['ID', 'UF_SURNAME', 'UF_NAME', 'UF_PATRONYMIC', 'UF_PROCEDURE_ID'];
    $filter = ['=ID' => '1' ];
    $order = ['ID' => 'ASC'];
    return $this->getDoctors($filter, $select, $order);
  }
	private function getDoctors($select=[], $filter=['ACTIVE' => 'Y'], $order=['ID' => 'Y'])
  {
    if(count($select) == 0){
      $select = [
        'ID',
        'NAME',
        'UF_SURNAME',
        'UF_NAME',
        'UF_PATRONYMIC',
        'UF_PROCEDURE_ID.ELEMENT'
      ];
    }
    $doctors = \Bitrix\Iblock\Elements\ElementDoctorsTable::query()
      ->setSelect($select)
      ->setFilter($filter)
      ->setOrder($order)
      ->fetchCollection();

    foreach ($doctors as $doctor){
      $doctorName = $doctor->get('UF_SURNAME') . ' ' . $doctor->get('UF_NAME') . ' ' . $doctor->get('UF_PATRONYMIC');
      $doctorProcedures = [];
      foreach($doctor->getUfProcedureId()->getAll() as $prItem) {
        //var_dump($prItem->getId().' - '.$prItem->getElement()->getName());
        $doctorProcedures[] = [
          'name'=> $prItem->getElement()->getName(),
          'id' => $prItem->getElement()->getId()
        ];
      }
       $doctors[$doctorName]['PROCEDURES'] = $doctorProcedures;
    }

    return $doctors;

    /*
    $select[] = 'PROCEDURE.ELEMENT.NAME';
    $query = new Query(
      DoctorsTable::getEntity()
    );
    $query->setSelect($select)
      ->setFilter($filter)
      ->setOrder($order);
    $query->registerRuntimeField(
      'PROCEDURE',
      array(
        'Models\Lists\ProceduresPropertyValuesTable',
        'reference' => array('=this.UF_PROCEDURE_ID' => 'ref.ID'),
        'join_type' => 'INNER'
      )
    );

    echo '<pre>' . $query->getQuery() . '</pre>';

// выполняем запрос
    $result = $query->exec();
    var_dump($result->fetchAll());
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
  public function addDoctorsAction()
  {
    return true;
  }
	private function addtDoctors()
  {
    return true;
  }
  public function deleteDoctorsAction()
  {
    return true;
  }
	private function deleteDoctors()
  {

  }
	public function executeComponent()
    {
        $cache = \Bitrix\Main\Data\Cache::createInstance();

        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

        if ($cache->initCache($this->arParams['CACHE_TIME'], 'Doctors' . CurrentUser::get()->getId())) {
            $this->arResult = $cache->getVars();
        } else {
   
            $this->arResult['ITEMS'] = $this->getDoctors();

            $cache->endDataCache($this->arResult);

            $this->includeComponentTemplate();

        }
    }
}