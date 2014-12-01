<?php
/**
 * LibraryOwnsType Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class LibraryOwnsType extends BaseEntityAbstract
{
	const ID_ONLINE_VIEW_COPIES = 1;
	const ID_DOWNLOAD_COPIES = 2;
	const ID_BORROW_TIMES = 3;
	/**
	 * The cached types
	 * 
	 * @var Multiple::LibraryOwnsType
	 */
	private static $_types = array();
    /**
     * The array of LibraryOwns
     * 
     * @var Multiple:LibraryOwns
     */
    protected $libraryOwns;
    /**
     * The name of the library owns
     * 
     * @var string
     */
    private $name;
    /**
     * The code for libraryownstype
     * 
     * @var string
     */
    private $code;
    /**
     * Getter for the name
     * 
     * @return string
     */
    public function getName() 
    {
        return $this->name;
    }
    /**
     * Setter for the name
     * 
     * @param string $value The name
     * 
     * @return LibraryOwnsType
     */
    public function setName($value) 
    {
        $this->name = $value;
        return $this;
    }
    /**
     * Getter for the code
     * 
     * @return string
     */
    public function getCode() 
    {
        return $this->code;
    }
    /**
     * Setter for the code
     * 
     * @param string $value The code
     * 
     * @return LibraryOwnsType
     */
    public function setCode($value) 
    {
        $this->code = $value;
        return $this;
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntityAbstract::__toString()
     */
	public function __toString()
	{    
		return $this->getName();
	}
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__loadDaoMap()
     */
    public function __loadDaoMap()
    {
        DaoMap::begin($this, 'lib_own_type');
        DaoMap::setStringType('name');
        DaoMap::setStringType('code');
        parent::__loadDaoMap();
    
        DaoMap::createUniqueIndex('code');
        DaoMap::createIndex('name');
        DaoMap::commit();
    }
    /**
     * Getting the type by code
     *
     * @param string $code The unique code for the type
     *
     * @return NULL|LibraryOwnsType
     */
    public static function getTypeByCode($code)
    {
    	if(!isset(self::$_types[$code]))
    	{
    		$results = self::getAllByCriteria('code = ?', array(trim($code)), true, 1, 1);
    		if(count($results) === 0)
    			return null;
    		self::$_types[$code] = $results[0];
    	}
    	return self::$_types[$code];
    }
}