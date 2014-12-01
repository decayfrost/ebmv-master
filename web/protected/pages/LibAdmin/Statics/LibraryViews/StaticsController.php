<?php
/**
 * This is the statics controls
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class StaticsController extends StaticsPageAbstract
{
	/**
	 * The selected Menu Item name
	 *
	 * @var string
	 */
	public $menuItemCode = 'statics.library.views';
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= 'pageJs';
		$js .= '.load({});';
		return $js;
	}
	/**
	 * (non-PHPdoc)
	 * @see StaticsPageAbstract::getData()
	 */
	public function getData($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$timeRange = $this->_getXnames();
			$names = array_keys($timeRange);
			$series = array();
			foreach(ProductStaticsType::getAll() as $type)
			{
				$series[] = array('name' => $type->getName(), 'data' => $this->_getSeries($timeRange, $timeRange[$names[0]]['from'], $timeRange[$names[count($names) - 1 ]]['to'], $type->getId()));
			}
	
			$results = array(
					'chart' => array(
							'type' => 'line'
					),
					'title' => array(
							'text' => Core::getLibrary()->getName() . ': mthly statics',
							'x'    => -20
					),
					'subtitle' => array(
							'text' => 'Total counts of all product in each month',
							'x'    => -20
					),
					'xAxis' => array(
							'categories' => $names
					),
					'yAxis' => array(
							'title' => array(
									'text' => 'No of all products'
							)
					),
					'series' => $series
			);
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	
	private function _getXnames()
	{
		$names = array();
		$_12mthAgo = new UDate();
		$_12mthAgo->modify('-11 month');
		for($i = 0; $i<12; $i++)
		{
			$from = new UDate(trim($_12mthAgo->format('Y-m-01 00:00:00')));
			$to = new UDate(trim($_12mthAgo->modify('+1 month')->format('Y-m-01 00:00:00')));
			$names[trim($from->format('M/Y'))] = array('from' => trim($from), 'to' => trim($to));
		}
		return $names;
	}
	
	private function _getSeries($groupFrame, $from, $to, $typeId = '')
	{
		$select = array();
		foreach($groupFrame as $index => $time)
			$select[] = 'sum(if((created >= "' . $time['from'] . '" && created < "' . $time['to'] . '"), value , 0)) `' . $index . '`';
		$where = array('active = 1');
		if(trim($typeId) !== '')
			$where[] = 'typeId = ' . trim($typeId);
		$sql = "select " . implode(', ', $select) . ' from `productstaticslog` where ' . implode(' AND ', $where) . ' and libraryId = ? and created >=? and created < ?';
		$row = Dao::getSingleResultNative($sql, array(Core::getLibrary()->getId(), trim($from), trim($to)), PDO::FETCH_NUM);
		$return = array();
		foreach($row as $col)
		{
			$return[] = intval($col);
		}
		return $return;
	}
}