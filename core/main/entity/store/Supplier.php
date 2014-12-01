<?php
/**
 * Supplier Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Supplier extends BaseEntityAbstract
{
	const ID_CIO = 10;
	/**
	 * The informaction cacher
	 * 
	 * @var array
	 */
	private $_info;
	/**
     * The name of the supplier
     * 
     * @var string
     */
    private $name;
    /**
     * The supplierConnector
     * 
     * @var string
     */
    private $connector;
    /**
     * The suppliers information
     * 
     * @var multiple:SupplierInfo
     */
    protected $supplierInfo;
	
	/**
	 * Getter for the title
	 * 
	 * @return string
	 */
	public function getName()
	{
	    return $this->name;
	}
	
	/**
	 * Setter for 
	 * 
	 * @param string $name The name of supplier
	 * 
	 * @return Supplier
	 */
	public function setName($name)
	{
	    $this->name = $name;
	    return $this;
	}
	/**
	 * Getter for the supplierLocation
	 * 
	 * @return string
	 */
	public function getConnector()
	{
		if(!class_exists($this->connector))
			throw new CoreException("System Error: " . $this->connector . " does NOT exsits!");
	    return $this->connector;
	}
	
	/**
	 * Setter for connector
	 * 
	 * @param string $connector The connector script for this supplier
	 * 
	 * @return Supplier
	 */
	public function setConnector($connector)
	{
	    $this->connector = $connector;
	    return $this;
	}
	/**  
	 * Getters for the supplier Information
	 * 
	 * @return multiple:SupplierInfo
	 */  
	public function getSupplierInfo() 
	{
		$this->loadOneToMany('supplierInfo');
	    return $this->supplierInfo;
	}
	/**
	 * Setters for the supplier information
	 * 
	 * @param array $value The supplier information array
	 * 
	 * @return Supplier
	 */
	public function setSupplierInfo($value) 
	{
	    $this->supplierInfo = $value;
	    return $this;
	}
	/**
	 * Getting the info
	 *
	 * @param string $typeCode  The code of the SupplierInfoType
	 * @param string $separator The separator of the returned attributes, in case there are multiple
	 * @param bool   $reset     Forcing to get the information from DB
	 *
	 * @return Ambigous <>
	 */
	public function getInfo($typeCode, $separator = ',', $reset = false)
	{
		if(!isset($this->_info[$typeCode]) || $reset === true)
		{
			$sql = 'select group_concat(si.value separator ?) `info` from supplierinfo si inner join supplierinfotype sit on (sit.id = si.typeId and sit.code = ?) where si.active = 1 and si.supplierId = ?';
			$result = Dao::getSingleResultNative($sql, array($separator, $typeCode, $this->getId()), PDO::FETCH_ASSOC);
			$this->_info[$typeCode] = $result['info'];
		}
		return $this->_info[$typeCode];
	}
	/**
	 * Getting the supplier's products
	 * 
	 * @param array       $excludePids
	 * @param array       $typeIds
	 * @param int         $pageNo      The page number
	 * @param int         $pageSize    The page size
	 * @param array       $orderBy     The order by array
	 */
	public function getProducts(array $excludePids = array(), array $typeIds = array(), $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, array $orderBy = array(), $activeOnly = true, &$stats = array())
	{
		if(trim( $this->getId()) === '')
			return array();
		$where = 'pro.supplierId = ?';
		$params = array($this->getId());
		if(count($typeIds) > 0)
		{
			$where .= ' AND pro.productTypeId in (' . implode(',', array_fill(0, count($typeIds), '?')) . ')';
			$params = array_merge($params, $typeIds);
		}
		if(count($excludePids) >0)
		{
			$where .= " AND pro.id not in (" . implode(',', array_fill(0, count($excludePids), '?')) . ')';
			$params = array_merge($params, $excludePids);
		}
		return Product::getAllByCriteria($where, $params, $activeOnly, $pageNo, $pageSize, $orderBy, $stats);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = array(), $reset = false)
	{
		$array = array();
		if(!$this->isJsonLoaded($reset))
		{
			$infoArray = array();
			$sql = "select distinct supIn.id `infoId`, supIn.value `infoValue`, supInType.id `typeId`, supInType.code `typeCode`, supInType.name `typeName` from supplierinfo supIn inner join supplierinfotype supInType on (supIn.typeId = supInType.id) where supIn.supplierId = ? and supIn.active = 1";
			$result = Dao::getResultsNative($sql, array($this->getId()), PDO::FETCH_ASSOC);
			foreach($result as $row)
			{
				if(!isset($infoArray[$row['typeCode']]))
					$infoArray[$row['typeCode']] = array();
				$infoArray[$row['typeCode']][] = array("id" => $row['infoId'], "value" => $row["infoValue"], "type" => array("id" => $row["typeId"], "name" => $row["typeName"], 'code' => $row['typeCode']));
			}
			$array['info'] = $infoArray;
			
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'supp');
		DaoMap::setStringType('name','varchar', 200);
		DaoMap::setStringType('connector','varchar', 200);
		DaoMap::setOneToMany('supplierInfo', 'supplierInfo', 'sup_info');
		parent::__loadDaoMap();
		
		DaoMap::createIndex('name');
		DaoMap::createIndex('connector');
		
		DaoMap::commit();
	}
}

?>