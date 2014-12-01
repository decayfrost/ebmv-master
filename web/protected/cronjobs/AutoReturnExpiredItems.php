<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class AutoReturnExpiredShelfItems
{
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
	/**
	 * Run this script
	 * 
	 * @throws Exception
	 */
	public static function run()
	{
		$now = new UDate();
		$sql = 'select * from Structureproductshelfitem where expiryTime < ?';
		foreach(Dao::getResultsNative($sql, array(trim($now))) as $shelfItemId)
		{
			try
			{
				Dao::beginTransaction();
				$shelfItem = ProductShelfItem::get($shelfItemId);
				if(!$shelfItem instanceof ProductShelfItem)
					throw new Exception('Invalid ProductShelfItem(ID=' . $shelfItemId . ')');
				$user = $shelfItem->getOwner();
				$lib = $user->getLibrary();
				ProductShelfItem::cleanUpShelfItems($user);
				ProductShelfItem::returnItem($user, $shelfItem->getProduct(), $lib);
				SupplierConnectorAbstract::getInstance($shelfItem->getProduct()->getSupplier(), $lib)->returnProduct($shelfItem->getProduct(), $user)
					->removeBookShelfList($user, $shelfItem->getProduct());
				Log::LogEntity($lib, $shelfItemId, 'ProductShelfItem', 'Auto Returned ShelfItem(ID' . $shelfItemId . ', ProductID=' . $shelfItem->getProduct()->getId(), ', OwnerID=' . $user->getId() . ')' , Log::TYPE_AUTO_EXPIRY);
				Dao::commitTransaction();
			}
			catch (Exception $ex)
			{
				Dao::rollbackTransaction();
				Log::LogEntity($lib, $shelfItemId, 'ProductShelfItem', 'ERROR: ' . $ex->getMessage() . '. Trace:' . $ex->getTraceAsString(), Log::TYPE_AUTO_EXPIRY);
				continue;
			}
		}
		
		self::showLogs(Log::getTransKey());
	}
}

AutoReturnExpiredShelfItems::run();