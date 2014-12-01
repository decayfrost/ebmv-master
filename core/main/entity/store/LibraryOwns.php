<?php
/**
 * LibraryOwns Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class LibraryOwns extends BaseEntityAbstract
{
    /**
     * The product that a library owns
     * 
     * @var Product
     */
    protected $product;
    /**
     * The library
     * 
     * @var Library
     */
    protected $library;
    /**
     * The LibraryOwnsType
     * 
     * @var LibraryOwnsType
     */
    protected $type;
    /**
     * The available copies for library owns
     * 
     * @var int
     */
    private $avail;
    /**
     * The total copies for library owns
     * 
     * @var int
     */
    private $total;
    /**
     * Getter for the product
     * 
     * @return Product
     */
    public function getProduct() 
    {
    	$this->loadManyToOne('product');
        return $this->product;
    }
    /**
     * Setter for the product
     * 
     * @param Product $value The Product
     * 
     * @return LibraryOwns
     */
    public function setProduct($value) 
    {
        $this->product = $value;
        return $this;
    }
    /**
     * Getter for the library
     * 
     * @return Library
     */
    public function getLibrary() 
    {
    	$this->loadManyToOne('library');
        return $this->library;
    }
    /**
     * Setter for the library
     * 
     * @param Library $value The Library
     * 
     * @return LibraryOwns
     */
    public function setLibrary($value) 
    {
        $this->library = $value;
        return $this;
    }
    /**
     * Getter for the avail
     * 
     * @return number
     */
    public function getAvail() 
    {
        return $this->avail;
    }
    /**
     * Setter for avail
     * 
     * @param int $value The avail
     * 
     * @return LibraryOwns
     */
    public function setAvail($value) 
    {
        $this->avail = $value;
        return $this;
    }
    /**
     * Getter for the total
     * 
     * @return number
     */
    public function getTotal() 
    {
        return $this->total;
    }
    /**
     * Setter for the total
     * 
     * @param int $value The total
     * 
     * @return LibraryOwns
     */
    public function setTotal($value) 
    {
        $this->total = $value;
        return $this;
    }
    /**
     * Getter for the library owns type
     * 
     * @return LibraryOwnsType
     */
    public function getType() 
    {
        return $this->type;
    }
    /**
     * Setter for the library owns type
     * 
     * @param LibraryOwnsType $value The LibraryOwnsType
     * 
     * @return LibraryOwns
     */
    public function setType(LibraryOwnsType $value) 
    {
        $this->type = $value;
        return $this;
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
	    	$array['type'] = $this->getType()->getJson();
	    }
	    return parent::getJson($array, $reset);
	}
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__loadDaoMap()
     */
    public function __loadDaoMap()
    {
        DaoMap::begin($this, 'lib_own');
        DaoMap::setManyToOne('library', 'Library');
        DaoMap::setManyToOne('product', 'Product');
        DaoMap::setManyToOne('type', 'LibraryOwnsType');
        DaoMap::setIntType('avail');
        DaoMap::setIntType('total');
        parent::__loadDaoMap();
    
        DaoMap::createIndex('avail');
        DaoMap::createIndex('total');
        DaoMap::commit();
    }
    /**
     * Updating the libraryowns
     *
     * @param Product         $product
     * @param Library         $lib
     * @param int             $avail
     * @param int             $total
     * @param LibraryOwnsType $type
     *
     * @return bool
     */
    public static function updateLibOwns(Product $product, Library $lib, $avail, $total, LibraryOwnsType $type = null)
    {
    	$where = 'productId = ? and libraryId = ?';
    	$params = array($product->getId(), $lib->getId());
    	if($type instanceof LibraryOwnsType)
    	{
    		$where .= ' AND typeId = ?';
    		$params[] = $type->getId();
    	}
    	self::updateByCriteria('avail = ?, total = ?', $where, $params);
    	return true;
    }
}