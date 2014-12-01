<?php
class AdminSupplierController extends CrudPageAbstract
{
	/**
	 * The selected Menu Item name
	 *
	 * @var string
	 */
	public $menuItemCode = 'suppliers';
	public function __construct()
    {
    	parent::__construct();
    }
    
    public function onPreInit($param)
    {
    	parent::onPreInit($param);	
    }
    
    public function onInit($param)
    {
    	parent::onInit($param);
    }
    
    public function onLoad($param)
    {
    	parent::onLoad($param);
    }
	
	protected function _getEndJs()
    {
        $pageNumber = 1;
        $pageSize = 10;
        $supplierId = 0;
    	
    	$js = parent::_getEndJs();
        $js .= 'pageJs.resultDivId="allSupplierDiv";';
        $js .= 'pageJs.types = ' . json_encode($this->_getInfoTypes()) . ';';
        $js .= 'pageJs.showItems(' . $pageNumber . ', ' . $pageSize . ', ' . $supplierId . ');';
        return $js;
    }
    /**
     * Getting the SupplierInfoTypes
     * 
     * @return multitype:NULL
     */	
    private function _getInfoTypes()
    {
        $array = array();
        foreach(SupplierInfoType::getAll() as $type)
        	$array[] = $type->getJson();
        return $array;
    }
    /**
     * (non-PHPdoc)
     * @see CrudPageAbstract::getItems()
     */	
    public function getItems($sender, $param)
    {
    	$result = $errors = $supplierArray = array();
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
    		$supplierId = (isset($param->CallbackParameter->itemId) && trim($param->CallbackParameter->itemId) !== '' && is_numeric($param->CallbackParameter->itemId)) ? trim($param->CallbackParameter->itemId) : '0';
    		$stats = array();
    		if($supplierId === '' || $supplierId === '0')
    			$supplierArray = Supplier::getAll(false, $pageNumber, $pageSize, array(), $stats);
    		else
    			$supplierArray[] = Supplier::get($supplierId);
    		$result['pagination'] = $stats;
    		$items = array();
    		foreach($supplierArray as $supplier)
    			$items[] = $supplier->getJson();
    		$result['items'] = $items;
    	}
    	catch(Exception $ex)
    	{
    		$errors[] = $ex->getMessage() . $ex->getTraceAsString();
    	}
    	$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
    }
	/**
	 * (non-PHPdoc)
	 * @see CrudPageAbstract::saveItems()
	 */
	public function saveItems($sender, $param)
    {
    	$result = $errors = $supplierArray = array();
    	try
    	{
    		Dao::beginTransaction();
    		if(!isset($param->CallbackParameter->id))
    			throw new Exception("System Error: No item id passed in!");
    		$supplier = ($supplier = Supplier::get(trim($param->CallbackParameter->id))) instanceof Supplier ? $supplier : new Supplier();
    		$supplier->setName(trim($param->CallbackParameter->name));
    		try
    		{
    			if(!class_exists($connector = trim($param->CallbackParameter->connector)) )
    				throw new Exception($connector . " does NOT exsit!");
    		} 
    		catch (Exception $e)
    		{
    			throw new Exception("Connector Script: " . $connector . " does NOT exsit!" . $e->getMessage(), 0, $e);
    		} 
    		
    		$supplier->setConnector($connector)
    			->setActive(strtolower(trim($param->CallbackParameter->active)) === 'on')
    			->save();
    		foreach($param->CallbackParameter->info as $info)
    		{
    			$supplierInfo = (($supplierInfo = SupplierInfo::get(trim($info->id))) instanceof SupplierInfo ? $supplierInfo : new SupplierInfo());
    			$supplierInfo->setType(SupplierInfoType::get(trim($info->typeId)))
    				->setValue(trim($info->value))
    				->setSupplier($supplier)
    				->setActive(trim($info->active) === '1')
    				->save();
    		}
    		Dao::commitTransaction();
    		$result['items'] = array($supplier->getJson());
    	}
    	catch(Exception $ex)
    	{
    		Dao::rollbackTransaction();
    		$errors[] = $ex->getMessage() ;
    	}
    	$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
    }
	/**
	 * (non-PHPdoc)
	 * @see CrudPageAbstract::saveItems()
	 */
	public function delItems($sender, $param)
    {
    	$result = $errors = $supplierArray = array();
    	try
    	{
    		Dao::beginTransaction();
    		if(!isset($param->CallbackParameter->itemIds))
    			throw new Exception("System Error: No item ids passed in!");
    		$itemIds = $param->CallbackParameter->itemIds;
    		Supplier::updateByCriteria('active = 0', 'id in (' . implode(', ', array_fill(0, count($itemIds), '?')). ')', $itemIds);
    		Dao::commitTransaction();
    	}
    	catch(Exception $ex)
    	{
    		Dao::rollbackTransaction();
    		$errors[] = $ex->getMessage() . $ex->getTraceAsString();
    	}
    	$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
    }
}
?>