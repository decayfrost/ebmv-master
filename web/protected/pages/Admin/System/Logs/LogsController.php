<?php
class LogsController extends CrudPageAbstract
{
	/**
     * The selected Menu Item name
     * 
     * @var string
     */
	public $menuItemCode = 'logs';
	/**
	 * (non-PHPdoc)
	 * @see FrontEndPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
	}
	/**
	 * (non-PHPdoc)
	 * @see CrudPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$pageNumber = 1;
		$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
		 
		$js = parent::_getEndJs();
		$js .= 'pageJs.resultDivId="itemListDiv";';
		$js .= 'pageJs.showItems(' . $pageNumber . ', ' . $pageSize . ', 0);';
		return $js;
	}
	/**
	 * (non-PHPdoc)
	 * @see CrudPageAbstract::getItems()
	 */
	public function getItems($sender, $param)
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
			$itemsArray = Log::getAll(false, $pageNumber, $pageSize, array(), $stats);
			$result['pagination'] = $stats;
			$items = array();
			foreach($itemsArray as $item)
				$items[] = $item->getJson();
			$result['items'] = $items;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
}