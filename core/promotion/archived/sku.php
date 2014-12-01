<?php
require_once dirname(__FILE__) . '/../main/bootstrap.php';

class Fix
{
	public static function run()
	{
		$array = self::_getProducts();
		foreach($array as $productId => $info)
		{
			try{
				Dao::beginTransaction();
				
				Product::updateByCriteria('suk=?', 'id = ?', array(Product::formatSKU($info['isbn'], $info['cno']), $productId));
				$ids = array_unique($info['ids']);
				if(count($ids) > 1)
				{
					echo 'update product_category set productId = ' . $productId . ' where productId in (' . implode(', ', $ids) . ");\n";
					echo 'update language_product set productId = ' . $productId . ' where productId in (' . implode(', ', $ids) . ");\n";
					echo 'update libraryowns set productId = ' . $productId . ' where productId in (' . implode(', ', $ids) . ");\n";
					echo 'update productstatics set productId = ' . $productId . ' where productId in (' . implode(', ', $ids) . ");\n";
					echo 'update productstaticslog set productId = ' . $productId . ' where productId in (' . implode(', ', $ids) . ");\n";
					
					echo 'delete from productattribute where productId in (' . implode(', ', $ids) . ");\n";
					echo 'delete from product where id in (' . implode(', ', $ids) . ");\n";
					
				}
				
				Dao::commitTransaction();
			} catch (Exception $ex) {
				Dao::rollbackTransaction();
				echo $ex->getMessage() . "\n";
				echo $ex->getTraceAsString() . "\n";
			}
		}
	}
	
	private static function _getProducts()
	{
		$sql = "select productId, attribute, typeId from productattribute where active = 1 and typeId in (?, ?)";
		$result = Dao::getResultsNative($sql, array(ProductAttributeType::ID_ISBN, ProductAttributeType::ID_CNO));
		$array = array();
		foreach($result as $row)
		{
			$productId = $row['productId'];
			if(!isset($array[$productId]))
				$array[$productId] = array('isbn' => '', 'cno' => '', 'ids' => array());
			if(intval($row['typeId']) === ProductAttributeType::ID_ISBN)
				$array[$productId]['isbn'] = trim($row['attribute']);
			else if(intval($row['typeId']) === ProductAttributeType::ID_CNO)
				$array[$productId]['cno'] = trim($row['attribute']);
			
			$array[$productId]['ids'][] = $productId;
		}
		return $array;
	}
}
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
Fix::run();