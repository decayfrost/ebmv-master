<?php
/** Library Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Library extends BaseEntityAbstract
{
	/**
	 * The ID of the admin library site
	 */
	const ID_ADMIN_LIB = 1;
	/**
	 * The name of the Library
	 *
	 * @var string
	 */
	private $name;
	/**
	 * 
	 * @var unknown
	 */
	private $connector;
	/**
	 * The userAccounts that the userAccounts belongs to
	 *
	 * @var multiple:UserAccount
	 */
	protected $userAccounts;
	/**
	 * The infor this library has
	 *
	 * @var multiple:LibraryInfo
	 */
	protected $infos;
	/**
	 * registry of infos
	 * 
	 * @var array
	 */
	private $_info = array();
	/**
	 * getter Name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	/**
	 * setter Name
	 *
	 * @param string $Name The name of the role
	 *
	 * @return Role
	 */
	public function setName($Name)
	{
		$this->name = $Name;
		return $this;
	}
	/**
	 * Getter for the LibraryConnector
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
	 * @param string $connector The connector script for this library
	 *
	 * @return Supplier
	 */
	public function setConnector($connector)
	{
		$this->connector = $connector;
		return $this;
	}
	/**
	 * Getter for the Useraccounts
	 * @return multiple:UserAccount
	 */
	public function getUserAccounts()
	{
		$this->loadOneToMany('userAccounts');
		return $this->userAccounts;
	}
	/**
	 * Setter for the useraccounts
	 * 
	 * @param array $userAccounts The user acocunts
	 * 
	 * @return Library
	 */
	public function setUserAccounts($userAccounts)
	{
		$this->userAccounts = $userAccounts;
		return $this;
	}
	/**
	 * Getter for the LibraryInfo
	 * @return multiple:LibraryInfo
	 */
	public function getInfos()
	{
		$this->loadOneToMany('infos');
		return $this->infos;
	}
	/**
	 * Setter for the LibraryInfo
	 * 
	 * @param array $infos The LibraryInfo
	 * 
	 * @return Library
	 */
	public function setInfos($infos)
	{
		$this->infos = $infos;
		return $this;
	}
	/**
	 * Getting the info
	 *
	 * @param string $typeCode  The code of the LibraryInfoType
	 * @param string $separator The separator of the returned attributes, in case there are multiple
	 * @param bool   $reset     Forcing the function to fetch values from the database
	 *
	 * @return Ambigous <>
	 */
	public function getInfo($typeCode, $separator = ',', $reset = false)
	{
		if(!isset($this->_info[$typeCode]) || $reset === true)
		{
			$sql = 'select group_concat(lib.value separator ?) `value` from libraryinfo lib inner join libraryinfotype libt on (libt.id = lib.typeId and libt.code = ?) where lib.active = 1 and lib.libraryId = ?';
			$result = Dao::getSingleResultNative($sql, array($separator, $typeCode, $this->getId()), PDO::FETCH_ASSOC);
			$this->_info[$typeCode] = $result['value'];
		}
		return $this->_info[$typeCode];
	}
	/**
	 * Whether the library is running in debug mode
	 * 
	 * @return bool
	 */
	public function isDebugMode()
	{
		return trim($this->getInfo('running_mode')) === '1';
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
			$sql = "select distinct libInfo.id `infoId`, libInfo.value `infoValue`, libInfoType.id `typeId`, libInfoType.name `typeName` from libraryinfo libInfo inner join libraryinfotype libInfoType on (libInfo.typeId = libInfoType.id) where libInfo.libraryId = ? and libInfo.active = 1";
			$result = Dao::getResultsNative($sql, array($this->getId()), PDO::FETCH_ASSOC);
			foreach($result as $row)
			{
				if(!isset($infoArray[$row['typeId']]))
					$infoArray[$row['typeId']] = array();
				$infoArray[$row['typeId']][] = array("id" => $row['infoId'], "value" => $row["infoValue"], "type" => array("id" => $row["typeId"], "name" => $row["typeName"]));
			}
			$array['info'] = $infoArray;
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'lib');
		DaoMap::setStringType('name', 'varchar', 255);
		DaoMap::setStringType('connector', 'varchar', 100);
		DaoMap::setOneToMany("userAccounts", "UserAccount","ua");
		DaoMap::setOneToMany("infos", "LibraryInfo","lib_info");
		parent::__loadDaoMap();

		DaoMap::createIndex('name');
		DaoMap::commit();
	}
	/**
	 * Getting the librarys from the code
	 *
	 * @param string $code
	 * @param bool   $searchActiveOnly
	 * @param int    $pageNo
	 * @param int    $pageSize
	 * @param array  $orderBy
	 *
	 * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getLibsFromCode($code, $searchActiveOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array())
	{
		return self::_getLibsFromInfo(array($code), 'aus_code', $searchActiveOnly, $pageNo, $pageSize, $orderBy);
	}
	/**
	 * Getting the librarys from the codes
	 *
	 * @param array  $codes
	 * @param bool   $searchActiveOnly
	 * @param int    $pageNo
	 * @param int    $pageSize
	 * @param array  $orderBy
	 *
	 * @return Ambigous <multiple:Library, Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getLibsFromCodes(array $codes, $searchActiveOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array())
	{
		return self::_getLibsFromInfo($codes, 'aus_code', $searchActiveOnly, $pageNo, $pageSize, $orderBy);
	}
	/**
	 * Getting the libraray from the code
	 *
	 * @param string $code
	 *
	 * @return Library|null
	 */
	public static function getLibFromCode($code)
	{
		$result = self::getLibsFromCode($code, true, 1, 1);
		return (count($result) === 0 ? null : $result[0]);
	}
	/**
	 * Getting the Libraries for the Li
	 * @param array  $infoValues       The value we are searching for
	 * @param int    $typeCode         The code of the type for the information
	 * @param bool   $searchActiveOnly Whether active only
	 * @param int    $pageNo           page number
	 * @param int    $pageSize         Page Size
	 * @param array  $orderBy          Order by what
	 *
	 * @return multiple:Library
	 */
	private static function _getLibsFromInfo($infoValues, $typeCode, $searchActiveOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array())
	{
		$query = self::getQuery();
		$query->eagerLoad('Library.infos', DaoQuery::DEFAULT_JOIN_TYPE, 'lib_info')->eagerLoad('LibraryInfo.type', DaoQuery::DEFAULT_JOIN_TYPE, 'lib_info_type');
		$params = array_merge(array($typeCode), $infoValues);
		return self::getAllByCriteria('lib_info_type.code = ? and lib_info.value in (' . implode(', ', array_fill(0, count($infoValues), '?')) . ')', $params, $searchActiveOnly, $pageNo, $pageSize, $orderBy);
	}
	/**
	 * Getting the library by the url
	 *
	 * @param string $url The url of the library
	 *
	 * @return multiple:Library
	 */
	public static function getLibByURL($url)
	{
		$result = self::_getLibsFromInfo(array($url), 'lib_url', true, 1, 1);
		return (count($result) === 0 ? null : $result[0]);
	}
}

?>