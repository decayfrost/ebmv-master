<?php
/**
 * Order Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Order extends BaseEntityAbstract
{
	const STATUS_OPEN = 'OPEN';
	const STATUS_CACNELLED = 'CANCELLED';
	const STATUS_CLOSED = 'CLOSED';
	const STATUS_SUBMITTED = 'SUBMITTED';
	/**
	 * The status of the order
	 * 
	 * @var string
	 */
	private $status = Order::STATUS_OPEN;
    /**
     * The no of the order No
     * 
     * @var string
     */
    private $orderNo = '';
    /**
     * The library this order is for
     * 
     * @var Library
     */
    protected $library;
    /**
     * The orderItems
     * 
     * @var multiple:OrderItem
     */
    protected $items;
    /**
     * the comments of this order
     * 
     * @var string
     */
    private $comments = '';
    /**
     * when this order is submitted
     * 
     * @var UDate
     */
    private $submitDate = null;
    /**
     * Who submitted this order
     * 
     * @var UserAccount
     */
    protected $submitBy = null;
    /**
     * Getter for submitDate
     * 
     * @return UDate
     */
    public function getSubmitDate()
    {
    	if(trim($this->submitDate) === '')
    		return null;
    	return new UDate(trim($this->submitDate));
    }
    /**
     * Setter for the submitDate
     * 
     * @param mixed $value
     * 
     * @return Order
     */
    public function setSubmitDate($value)
    {
    	$this->submitDate = $value;
    	return $this;
    }
    /**
     * Getter for submitBy
     * 
     * @return UserAccount
     */
    public function getSubmitBy()
    {
    	$this->loadManyToOne('submitBy');
    	return $this->submitBy;
    }
    /**
     * Setter for the submitBy
     * 
     * @param mixed $value
     * 
     * @return Order
     */
    public function setSubmitBy($value)
    {
    	$this->submitBy = $value;
    	return $this;
    }
    /**
     * Getter for orderNo
     *
     * @return string
     */
    public function getOrderNo() 
    {
        return trim($this->orderNo);
    }
    /**
     * Setter for orderNo
     *
     * @param string $value The orderNo
     *
     * @return Order
     */
    public function setOrderNo($value) 
    {
        $this->orderNo = $value;
        return $this;
    }
    /**
     * Getter for library
     *
     * @return Library
     */
    public function getLibrary() 
    {
    	$this->loadManyToOne('library');
        return $this->library;
    }
    /**
     * Setter for library
     *
     * @param Library $value The library
     *
     * @return Order
     */
    public function setlibrary(Library $value) 
    {
        $this->library = $value;
        return $this;
    }
    /**
     * Getter for items
     *
     * @return Multiple::OrderItem
     */
    public function getItems() 
    {
    	$this->loadOneToMany('items');
        return $this->items;
    }
    /**
     * Setter for items
     *
     * @param array $value The items
     *
     * @return Order
     */
    public function setItems(array $value) 
    {
        $this->items = $value;
        return $this;
    }
    /**
     * Getting the order items for an order
     * 
     * @param string $activeOnly
     * @param int    $pageNo
     * @param int    $pageSize
     * @param array  $orderBy
     * @param array  $stats
     * 
     * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
     */
    public function getOrderItems($activeOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
    {
    	return OrderItem::getAllByCriteria('orderId = ?', array(trim($this->getId())), $activeOnly, $pageNo, $pageSize, $orderBy, $stats);
    }
    /**
     * Getter for status
     *
     * @return string
     */
    public function getStatus() 
    {
        return $this->status;
    }
    /**
     * Setter for status
     *
     * @param string $value The status
     *
     * @return Order
     */
    public function setStatus($value) 
    {
        $this->status = $value;
        return $this;
    }
    /**
     * Getter for comments
     *
     * @return string
     */
    public function getComments() 
    {
        return $this->comments;
    }
    /**
     * Setter for comments
     *
     * @param string $value The comments
     *
     * @return Order
     */
    public function setComments($value) 
    {
        $this->comments = $value;
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
	    	$array['items'] = array();
		    foreach($this->getItems() as $item)
		        $array['items'][] = $item->getJson();
		    $array['submitBy'] = $this->getSubmitBy() instanceof UserAccount ? $this->getSubmitBy()->getJson() : array();
	    }
	    return parent::getJson($array, $reset);
	}
    /**
     * (non-PHPdoc)
     * @see BaseEntityAbstract::postSave()
     */
    public function postSave()
    {
    	if(trim($this->getOrderNo()) === '')
    	{
    		$codes = explode(',', $this->getLibrary()->getInfo('aus_code'));
    		$this->setOrderNo($codes[0] . str_pad($this->getId(), 6, '0', STR_PAD_LEFT))
    			->save();
    	}
    }
    /**
     * Submit this order
     * 
     * @param UserAccount $user
     * 
     * @return Ambigous <BaseEntityAbstract, GenericDAO>
     */
    public function submit(UserAccount $user)
    {
    	return $this->setSubmitBy($user)
    		->setSubmitDate(new UDate())
    		->setStatus(Order::STATUS_SUBMITTED)
    		->save();
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__loadDaoMap()
     */
    public function __loadDaoMap()
    {
        DaoMap::begin($this, 'ord');
        DaoMap::setStringType('orderNo','varchar', 50);
        DaoMap::setStringType('status','varchar', 10);
        DaoMap::setManyToOne("library", "Library", 'ord_lib');
        DaoMap::setDateType('submitDate', 'datetime', true);
        DaoMap::setManyToOne("submitBy", "UserAccount", 'ord_ua', true);
        DaoMap::setOneToMany("items", "OrderItem", 'ord_items');
        DaoMap::setStringType('comments','varchar', 255);
        parent::__loadDaoMap();
    
        DaoMap::createIndex('orderNo');
        DaoMap::createIndex('status');
        DaoMap::createIndex('submitDate');
        DaoMap::commit();
    }
    /**
     * Getting the open order by library
     *
     * @param Library $lib The library this order is for
     *
     * @return Order|NULL
     */
    public static function getOpenOrder(Library $lib)
    {
    	$orders = self::getAllByCriteria('libraryId = ? and status = ?', array($lib->getId(), trim(Order::STATUS_OPEN)), true, 1, 1);
    	return  count($orders) === 0 ? null : $orders[0];
    }
    /**
     * Creating a new order
     * 
     * @param Library $lib
     * @param string $comments
     * 
     * @return Order
     */
    public static function create(Library $lib, $comments = '')
    {
    	$order = new Order();
    	return $order->setlibrary($lib)
    		->setComments($comments)
    		->save();
    }
    /**
     * Getting the order by order no
     * 
     * @param string $orderNo The order no
     * 
     * @return Ambigous <NULL, unknown>
     */
    public static function getOrderByOrderNo($orderNo)
    {
    	$orders = $class::getAllByCriteria('orderNo = ?', array(trim($orderNo)), true, 1, 1);
    	return count($orders) === 0 ? null : $orders[0];
    }
}