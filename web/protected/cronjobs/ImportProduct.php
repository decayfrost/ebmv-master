<?php
class ImportProduct
{
	const FLAG_START = 'Import Start';
	const FLAG_END = 'Import END';
	/**
	 * Getting the trans id from the log
	 * 
	 * @param string $salt
	 * 
	 * @return string
	 */
	public static function getLogTransId($salt = '')
	{
		return Log::getTransKey($salt);
	}
	/**
	 * The runner
	 * 
	 * @param array  $libCodes
	 * @param array  $supplierIds
	 * @param string $totalRecords
	 * 
	 * @return string
	 */
	public static function run(array $libCodes = array(), array $supplierIds = array(), $totalRecords = null)
	{
		ini_set('max_execution_time', 0);
		if(!Core::getUser() instanceof UserAccount)
			Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
		try
		{
			$startScript = new UDate();
			self::log( "== Start import script @ " . $startScript . "=============================", __FUNCTION__, self::FLAG_START);
			
			//loop through each library
			$libraries = self::_getLibs($libCodes);
			$suppliers = self::_getSuppliers($supplierIds) ;
			self::log( "  == Found " . count($libraries) . " libraries and " .  count($suppliers) . " suppliers to go through:", __FUNCTION__);
			foreach($libraries as $lib)
			{
				Core::setLibrary($lib);
				//loop through each supplier
				foreach($suppliers as $supplier)
				{
					self::_importProduct($supplier, $lib, $totalRecords);
				}
			}
		}
		catch(Exception $ex)
		{
			self::log('Import Script Error: ' . $ex->getMessage() . '. Trace: ' . $ex->getTraceAsString(), __FUNCTION__);
		}
		$finishScript = new UDate();
		$scriptRunningtime = $finishScript->diff($startScript);
		self::log( "== Finished import script @ " . $finishScript . "(Used: " . $scriptRunningtime->format("%H hours, %I minutes, %S seconds") . ")=============================", __FUNCTION__, self::FLAG_END);
		$transId = self::getLogTransId();
// 		echo self::showLogs($transId);
		return $transId;
	}
	
	private static function _importProduct(Supplier $supplier, Library $lib, $totalRecords)
	{
		$totalRecords = trim($totalRecords);
		$fullUpdate = ($totalRecords === '');
		
		self::log( "== import from " . __FUNCTION__, $supplier->getName());
			
		//if there is an error for supplier connector
		try {$script = SupplierConnectorAbstract::getInstance($supplier, $lib); }
		catch(Exception $ex)
		{
			self::log( "  :: " . $ex->getMessage() . ". Trace: " . $ex->getTraceAsString(), __FUNCTION__);
			continue;
		}
			
		$types = $script->getImportProductTypes();
		self::log( "  :: Got (" . count($types) . ") types to import:", __FUNCTION__);
		foreach($types as $type)
		{
			//getting how many record we need to run
			self::log( "  :: start download the xml for "  .$type->getName() ."...", __FUNCTION__);
			$productList = $script->getProductList(1, $fullUpdate ? 100 : trim($totalRecords), $type, !$fullUpdate);
			self::log( " downloaded.", __FUNCTION__);
		
			//process each record
			$childrenCount = count($productList);
			self::log("  :: Start to import (" . $childrenCount . ") products:", __FUNCTION__);
			for($i = 0; $i< $childrenCount; $i++)
			{
			self::log('    -- Importing Product No: ' . $i . " ... ", __FUNCTION__);
			try
			{
			self::log("    -- xml: " . ($productList[$i] instanceof SimpleXMLElement ? $productList[$i]->asXml() : $productList[$i]), __FUNCTION__);
			$script->importProducts($productList, $i);
			self::log("    -- Done",  __FUNCTION__);
			}
			catch(Exception $ex)
			{
					self::log("ERROR: " . $ex->getMessage() . ', Trace: ' . $ex->getTraceAsString(), __FUNCTION__);
					continue;
							}
			}
		
//			removing the un-imported products
// 			$ids = $supplier->getProducts($script->getImportedProductIds());
// 			if($fullUpdate === true && count($ids) > 0)
// 			{
// 				self::log( "  :: removing un-imported (" . count($ids) . ") product ids: " . implode(', ', $ids),  __FUNCTION__);
// 				$script->rmUnImportedProducts();
// 				self::log( "  :: done removing un-imported products.", __FUNCTION__);
// 			}
		}
	}
	/**
	 * Getting the suppliers
	 * 
	 * @param string $supplierIds
	 * 
	 * @throws Exception
	 * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	private static function _getSuppliers($supplierIds = null)
	{
		if(!is_array($supplierIds))
			throw new Exception("System Error: supplids has to be a array!");
		if($supplierIds === null || count($supplierIds) === 0)
			return Supplier::getAll();
		return Supplier::getAllByCriteria('id in (' . implode(', ', array_fill(0, count($supplierIds), '?')) . ')', $supplierIds);
	}
	/**
	 * getting the libraries
	 * 
	 * @param string $libCodes
	 * @throws Exception
	 * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	private static function _getLibs($libCodes = null)
	{
		if(!is_array($libCodes))
			throw new Exception("System Error: lib has to be a array!");
		if($libCodes === null || count($libCodes) === 0)
			return Library::getAll();
		return Library::getLibsFromCodes($libCodes);
	}
	/**
	 * Loging the messages
	 * 
	 * @param unknown $msg
	 * @param unknown $script
	 * 
	 */
	public static function log($msg, $funcName, $comments = '')
	{
		echo $msg . "\r\n";
		Log::logging(Library::get(Library::ID_ADMIN_LIB), 0, 'ImportProduct', $msg, Log::TYPE_PIMPORT, $comments,  $funcName);
	}
	/**
	 * Getting the logs
	 * 
	 * @param string $logKey
	 * @param string $lineBreaker
	 */
	public static function showLogs($logKey = '', $lineBreaker = "\r\n")
	{
		$logKey = (($logKey = trim($logKey)) === '' ? self::getLogTransId() : $logKey);
		$where = 'transId = ?';
		$logs = Log::getAllByCriteria($where, array($logKey));
		foreach($logs as $log)
		{
			echo $log . $lineBreaker;
		}
	}
}
