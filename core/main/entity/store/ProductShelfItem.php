<?php
/**
 * ProductShelfItem Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductShelfItem extends BaseEntityAbstract
{
	/**
	 * The status code for borrowed item
	 * @var int
	 */
	const ITEM_STATUS_BORROWED = 1;
	/**
	 * The status code for NOT borrowed item
	 * @var int
	 */
	const ITEM_STATUS_NOT_BORROWED = 0;
	/**
	 * The status code for expired item
	 * @var int
	 */
	const ITEM_STATUS_EXPIRED = 2;
    /**
     * The owner of the shelf
     * 
     * @var UserAccount
     */
    protected $owner;
    /**
     * The product this attribute is belonging to
     * 
     * @var Product
     */
    protected $product;
    /**
     * Status of the item
     * @var int
     */
    private $status = self::ITEM_STATUS_NOT_BORROWED;
    /**
     * The borrow time of that product
     * @var UDate
     */
    private $borrowTime;
    /**
	 * The expiry time of the product
     *
     * @var UDate
     */
    private $expiryTime = '9999-12-31 23:59:59';
    /**
     * Getter for the staus
     * @return number
     */
    public function getStatus() 
    {
    	return $this->status;
    }
    /**
     * Setter for the status
     * 
     * @param int $value The status of the item
     * 
     * @return ProductShelfItem
     */
    public function setStatus($value) 
    {
    	$this->status = $value;
    	return $this;
    }
    /**
     * Getter for the borrow time of the item
     * 
     * @return UDate
     */   
    public function getBorrowTime() 
    {
    	if(is_string($this->borrowTime))
    		$this->borrowTime = new UDate($this->borrowTime);
    	return $this->borrowTime;
    }
    /**
     * Setter for the borrow time of the item
     * 
     * @param string $value
     * 
     * @return ProductShelfItem
     */
    public function setBorrowTime($value) 
    {
    	$this->borrowTime = $value;
    	return $this;
    }
    /**
     * Getter for the expiry time
     * 
     * @return UDate
     */
    public function getExpiryTime() 
    {
    	if(is_string($this->expiryTime))
    		$this->expiryTime = new UDate($this->expiryTime);
        return $this->expiryTime;
    }
    /**
     * Setter for the expiry time
     * 
     * @param string $value The time value in unix format: 1901-01-01 00:00:00
     * 
     * @return ProductShelfItem
     */
    public function setExpiryTime($value) 
    {
        $this->expiryTime = $value;
        return $this;
    }
    /**
     * Getter for the owner
     * 
     * @return UserAccount
     */
    public function getOwner()
    {
        $this->loadManyToOne('owner');
        return $this->owner;
    }
    /**
     * Setter for the Owner
     * 
     * @param UserAccount $owner The owner of the shelf
     * 
     * @return ProductShelfItem
     */
    public function setOwner(UserAccount $owner)
    {
        $this->owner = $owner;
        return $this;
    }
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
     * @param Product $product The product
     * 
     * @return ProductShelfItem
     */
    public function setProduct($product)
    {
        $this->product = $product;
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
    		$array['product'] = array();
    		if(($product = $this->getProduct()) instanceof Product)
    			$array['product'] = $this->getProduct()->getJson();
    	}
    	return parent::getJson($array, $reset);
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__loadDaoMap()
     */
    public function __loadDaoMap()
    {
        DaoMap::begin($this, 'psitem');
        DaoMap::setManyToOne('product', 'Product');
        DaoMap::setManyToOne('owner', 'UserAccount');
        DaoMap::setIntType('status', 'int', 1);
        DaoMap::setDateType('borrowTime');
        DaoMap::setDateType('expiryTime');
        parent::__loadDaoMap();
        DaoMap::createIndex('status');
        DaoMap::createIndex('borrowTime');
        DaoMap::createIndex('expiryTime');
        DaoMap::commit();
    }
    /**
     * cleanup unused shelfitems
     *
     * @param UserAccount $user The owner of the shelfitem
     *
     * @return bool
     */
    public static function cleanUpShelfItems(UserAccount $user = null)
    {
    	if($user instanceof UserAccount)
    		$user->deleteInactiveShelfItems();
    	else
    		ProductShelfItem::deleteByCriteria('active = 0');
    	return true;
    }
    /**
     * Getting the shelf items
     *
     * @param UserAccount $user
     * @param Supplier    $supplier
     * @param number      $pageNo
     * @param number      $pageSize
     * @param array       $orderBy
     *
     * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
     */
    public static function getShelfItems(UserAccount $user, Supplier $supplier = null, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
    {
    	$where = 'psitem.ownerId = ?';
    	$params = array($user->getId());
    	if($supplier instanceof Supplier)
    	{
    		$query = self::getQuery();
    		$query->eagerLoad("ProductShelfItem.product", DaoQuery::DEFAULT_JOIN_TYPE, 'p');
    		$where .= ' AND p.SupplierId = ?';
    		$params[] = $supplier->getId();
    	}
    	return self::getAllByCriteria($where, $params, true, $pageNo, $pageSize, $orderBy, $stats);
    }
    /**
     * Adding a product onto shelf
     *
     * @param UserAccount $user
     * @param Product     $product
     * @param Library     $lib
     *
     * @throws Exception
     * @return bool
     */
    public static function addToShelf(UserAccount $user, Product $product, Library $lib)
    {
    	self::syncShelfItem($user, $product, new UDate(), ProductShelfItem::ITEM_STATUS_NOT_BORROWED, $lib);
    	return true;
    }
    /**
     * Borrow an item / add to our self
     *
     * @param UserAccount $user
     * @param Product     $product
     * @param Library     $lib
     * @param Supplier    $supplier
     *
     * @throws Exception
     * @return bool
     */
    public static function borrowItem(UserAccount $user, Product $product, Library $lib)
    {
    	self::syncShelfItem($user, $product, new UDate(), ProductShelfItem::ITEM_STATUS_BORROWED, $lib);
    	return true;
    }
    /**
     * Removing the item from the bookshelf
     *
     * @param UserAccount $user
     * @param Product     $product
     * @param Library     $lib
     *
     * @throws Exception
     * @return bool
     */
    public static function removeItem(UserAccount $user, Product $product, Library $lib)
    {
    	ProductShelfItem::updateByCriteria('`active` = 0', '`productId` = ? and `ownerId` = ?', array($product->getId(), $user->getId()));
    	return true;
    }
    /**
     * returnItem
     *
     * @param UserAccount $user
     * @param Product     $product
     * @param Library     $lib
     *
     * @throws Exception
     * @return bool
     */
    public static function returnItem(UserAccount $user, Product $product, Library $lib)
    {
    	ProductShelfItem::updateByCriteria('`status` = ?', '`productId` = ? and `ownerId` = ?', array(ProductShelfItem::ITEM_STATUS_NOT_BORROWED, $product->getId(), $user->getId()));
    	return true;
    }
    /**
     * synchronize shelf item with local database
     *
     * @param UserAccount $user
     * @param Product     $product
     * @param string      $borrowTime
     * @param int         $status
     *
     * @return bool
     */
    public static function syncShelfItem(UserAccount $user, Product $product, $borrowTime, $status, Library $lib)
    {
    	$where = '`productId` = ? and `ownerId` = ?';
    	$params = array($product->getId(), $user->getId());
    	$count = ProductShelfItem::countByCriteria($where, $params);
    	if($count == 0 )
    	{
    		$expiryTime = new UDate(trim($borrowTime));
    		$libraryLoanTime = $lib->getInfo('max_loan_time');
    		if(trim($libraryLoanTime) === '')
    			$libraryLoanTime = SystemSettings::getSettings(SystemSettings::TYPE_DEFAULT_MAX_LOAN_TIME);
    		$expiryTime->modify($libraryLoanTime);
    
    		$item = new ProductShelfItem();
    		$item->setOwner($user)
    			->setProduct($product)
    			->setBorrowTime(trim($borrowTime))
    			->setExpiryTime(trim($expiryTime))
    			->setStatus($status)
    			->save();
    	}
    	else
    		self::updateByCriteria('`status` = ?', $where, array_merge(array($status), $params));
    	return true;
    }
}