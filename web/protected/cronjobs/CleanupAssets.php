<?php
class CleanupAssets
{
	public static function run()
	{
		self::_log(__FUNCTION__, '== start @ ' . new UDate());
		try
		{
			//clean up DB data
			self::_log(__FUNCTION__, '== clean up DB data');
			self::_cleanupDBdata();
			
			//find out all the assets in the table that are not used by the system
			self::_log(__FUNCTION__, '== find out all the assets in the table that are not used by the system');
			$usedAssetIds = self::_getAllUnusedAssets();
			self::_log(__FUNCTION__, '	:: Got');
			self::_log(__FUNCTION__, '	' . print_r($usedAssetIds, true));
			
			self::_log(__FUNCTION__, '== remove those assets from DB and files');
			//removing all the unused assets from files and DB
			self::_removeAsset($usedAssetIds);
			
			//removing all zombie files
			self::_log(__FUNCTION__, '== remove all zombie files');
			$testedFiles = self::_rmAllUnusedAssetsFiles();
			self::_log(__FUNCTION__, '  :: ' . count($testedFiles) . ' file(s) tested!');

			//last checking products vs assets
			self::_log(__FUNCTION__, '== Summary: ');
			$diff = self::_showSummary($testedFiles);
			self::_log(__FUNCTION__, '== Final Difference: ' . $diff);
		}
		catch(Exception $ex)
		{
			self::_log(__FUNCTION__, '** Error: ' . $ex->getMessage());
			self::_log(__FUNCTION__, '   ' . $ex->getTraceAsString());
		}
		self::_log(__FUNCTION__, '== Finished @ ' . new UDate());
	}
	/**
	 * Showing the summary
	 * 
	 * @param array $testedFiles
	 * 
	 * @return number
	 */
	private function _showSummary($testedFiles)
	{
		$noOfTestedFiles = count($testedFiles);
		//trying to see the number of product matches with the number of files
		$sql = "select count(id) from product";
		$result = Dao::getSingleResultNative($sql, array(), PDO::FETCH_NUM);
		$testedFiles = self::_rmAllUnusedAssetsFiles();
		self::_log('	' . __FUNCTION__, '== GOT: ' . $result[0] . ' product(s) and ' . $noOfTestedFiles . ' file(s), difference: ' . ($diff = ($result[0] - $noOfTestedFiles)));
		//trying to see how many products that do NOT attrbites
		$sql = 'select count(p.id) from product p left join productattribute att on (att.productId = p.id and att.typeId in(?, ?)) where att.id is null';
		$result = Dao::getSingleResultNative($sql, array(ProductAttributeType::ID_IMAGE,ProductAttributeType::ID_IMAGE_THUMB), PDO::FETCH_NUM);
		self::_log('	' . __FUNCTION__, '== GOT: ' . $result[0] . ' product(s) without assets.');
		
		return ($diff - $result[0]);
	}
	/**
	 * Getting the log
	 * 
	 * @param sting $functName
	 * @param string $msg
	 */
	private function _log($functName, $msg)
	{
		echo $functName . ': ' . $msg . "\n\r";
	}
	/**
	 * Getting all the unused asset record in DB
	 * 
	 * @return array
	 */
	private static function _getAllUnusedAssets()
	{
		$sql = "select ass.assetId, ass.path from asset ass
			left join productattribute att on (att.attribute = ass.assetId and att.typeId IN (?, ?))
			where att.id is null";
		$return = array();
		foreach(Dao::getResultsNative($sql, array(ProductAttributeType::ID_IMAGE,ProductAttributeType::ID_IMAGE_THUMB )) as $row)
			$return[] = $row['assetId'];
		
		return $return;
	}
	/**
	 * Clean up the DB data
	 */
	private static function _cleanupDBdata()
	{
		//get all inactive product ids
		$sql = "select id from product where active = 0";
		$pIds = array_map(create_function('$a', 'return $a[0];'), Dao::getResultsNative($sql, array(), PDO::FETCH_NUM));
		$pIds[] = 0;
		
		if(count($pIds) > 0)
		{
			$where = 'productId in (' . implode(',', $pIds)  . ')';
			//delete productattributes that have inactive product
			Dao::deleteByCriteria(new DaoQuery('ProductAttribute'), 'active = 0 or ' . $where);
			//delete libraryowns
			Dao::deleteByCriteria(new DaoQuery('LibraryOwns'), 'active = 0 or  ' . $where);
			//delete productshelfitem
			Dao::deleteByCriteria(new DaoQuery('ProductShelfItem'), 'active = 0 or ' . $where);
			//delete productstatics
			Dao::deleteByCriteria(new DaoQuery('ProductStatics'), 'active = 0 or  ' . $where);
			//delete category_product
			Dao::deleteByCriteria('category_product',  $where);
			//delete language_product
			Dao::deleteByCriteria('language_product',  $where);
		}
		
		//delete inactive
		Dao::deleteByCriteria(new DaoQuery('Product'), 'active = 0');
		
		//delete any logs that older than a 1 month
		$oneMonthOld = new UDate();
		$oneMonthOld->modify('-1 month');
		Dao::deleteByCriteria(new DaoQuery('Log'), 'created < ?', array(trim($oneMonthOld)));
	}
	/**
	 * removing all the assets from db and files
	 * 
	 * @param array $assetIds The array of assetId
	 */
	private static function _removeAsset(array $assetIds)
	{
// 		self::_log(__FUNCTION__, '  :: removing assetIds: ');
		Asset::removeAssets($assetIds);
// 		self::_log(__FUNCTION__, '  :: finish removing assetIds: ');
	}
	/**
	 * removing all zombie files
	 */
	private static function _rmAllUnusedAssetsFiles()
	{
		$sql = "select value from supplierinfo where typeId = " . SupplierInfoType::ID_IMAGE_LOCATION;
		$totalFiles = array();
		foreach(Dao::getResultsNative($sql) as $row)
			self::_rmZombieFiles($row['value'], $totalFiles);
		return $totalFiles;
	}
	/**
	 * removing all zombie files under the root path
	 * 
	 * @param string $rootPath
	 */
	private static function _rmZombieFiles($rootPath, array &$totalFiles)
	{
// 		self::_log(__FUNCTION__, '  :: == removing files under: ' . $rootPath);
		foreach(glob($rootPath . DIRECTORY_SEPARATOR . '*', GLOB_BRACE) as $file)
		{
			if(is_file($file))
			{
				$totalFiles[] = $file;
				$assetId = basename($file);
				//self::_log(__FUNCTION__, '  :: == Got file(' . $assetId .') : ' . $file);
				if(!self::_checkAssetExsitsDb($assetId))
				{
					self::_log(__FUNCTION__, '  :: == removing file : ' . $file);
					unlink($file);
				}
			}
			else if(is_dir($file))
				self::_rmZombieFiles($file, $totalFiles);
		}
// 		self::_log(__FUNCTION__, '  :: == finished removing files under: ' . $rootPath);
	}
	/**
	 * Checking whether the assetId exsits in DB
	 * 
	 * @param string $assetId The assetId
	 * 
	 * @return boolean
	 */
	private static function _checkAssetExsitsDb($assetId)
	{
		$sql = 'select id from asset where assetId = ?';
		$result = Dao::getResultsNative($sql, array($assetId), PDO::FETCH_NUM);
		return count($result) > 0;
	}
}