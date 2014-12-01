<?php
/**
 * This is the Home page for statics admin
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class Controller extends LibAdminPageAbstract
{
	/**
	 * The selected Menu Item name
	 *
	 * @var string
	 */
	public $menuItemCode = 'statics';
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= 'pageJs.setCallbackId("getStats", "' . $this->getStatsBtn->getUniqueID() . '")';
		$js .= '.setHTMLIDs("top-viewed", "total-count")';
		$js .= '.load()';
		$js .= ';';
		return $js;
	}
	/**
	 * Getting the statics
	 * 
	 * @param unknown $sender
	 * @param unknown $param
	 */
	public function getStats($sender, $param)
	{
		$result = $errors = array();
		try 
		{
			$pageNumber = 1;
			$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
			if(isset($param->CallbackParameter->pagination))
			{
				$pagination = $param->CallbackParameter->pagination;
				$pageNumber = (isset($pagination->pageNo) && trim($pagination->pageNo) !== '' && is_numeric($pagination->pageNo)) ? trim($pagination->pageNo) : $pageNumber;
				$pageSize = (isset($pagination->pageSize) && trim($pagination->pageSize) !== '' && is_numeric($pagination->pageSize)) ? trim($pagination->pageSize) : $pageSize;
			}
			$stats = array();
			$products = Product::getMostPopularProducts(Core::getLibrary(), null, null, $pageNumber, $pageSize, array('pstats.value'=>'desc'), $stats);
			$result['items'] = array();
			foreach($products as $product)
			{
				$array = $product->getJson();
				$array['statics'] = array();
				foreach(ProductStaticsType::getAll() as $type)
				{
					$statics = $product->getStatic(Core::getLibrary(), $type);
					$array['statics'][$type->getId()] = ($statics instanceof ProductStatics ? $statics->getJson() : array());
				}
				$result['items'][] = $array;
			}
			$result['pagination'] =  $stats;
		}
		catch (Exception $e)
		{
			$errors[] = $e->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
}
?>