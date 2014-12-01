<?php
/**
 * OrderItem Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class OrderItem extends BaseEntityAbstract
{
    /**
     * The order this item belongs to
     * 
     * @var Order
     */
    protected $order;
    /**
     * The product
     * 
     * @var Product
     */
    protected $product;
    /**
     * The unit price
     * 
     * @var double
     */
    private $unitPrice;
    /**
     * The qty of the order
     * 
     * @var int
     */
    private $qty;
    /**
     * The total price of the order item
     * 
     * @var double
     */
    private $totalPrice;
    /**
     * Whether need MARC record for this item
     * 
     * @var bool
     */
    private $needMARCRecord = false;
    
    /**
     * Getter for order
     *
     * @return Order
     */
    public function getOrder() 
    {
    	$this->loadManyToOne('order');
        return $this->order;
    }
    /**
     * Setter for order
     *
     * @param Order $value The order
     *
     * @return OrderItem
     */
    public function setOrder(Order $value) 
    {
        $this->order = $value;
        return $this;
    }
    /**
     * Getter for product
     *
     * @return Product
     */
    public function getProduct() 
    {
    	$this->loadManyToOne('product');
        return $this->product;
    }
    /**
     * Setter for product
     *
     * @param Product $value The product
     *
     * @return OrderItem
     */
    public function setProduct(Product$value) 
    {
        $this->product = $value;
        return $this;
    }
    /**
     * Getter for unitPrice
     *
     * @return double
     */
    public function getUnitPrice() 
    {
        return $this->unitPrice;
    }
    /**
     * Setter for unitPrice
     *
     * @param double $value The unitPrice
     *
     * @return OrderItem
     */
    public function setUnitPrice($value) 
    {
        $this->unitPrice = $value;
        return $this;
    }
    /**
     * Getter for qty
     *
     * @return 
     */
    public function getQty() 
    {
        return $this->qty;
    }
    /**
     * Setter for qty
     *
     * @param int $value The qty
     *
     * @return OrderItem
     */
    public function setQty($value) 
    {
        $this->qty = $value;
        return $this;
    }
    /**
     * Getter for totalPrice
     *
     * @return double
     */
    public function getTotalPrice() 
    {
        return $this->totalPrice;
    }
    /**
     * Setter for totalPrice
     *
     * @param double $value The totalPrice
     *
     * @return OrderItem
     */
    public function setTotalPrice($value) 
    {
        $this->totalPrice = $value;
        return $this;
    }
    /**
     * Getter for needMARCRecord
     * 
     * @return needMARCRecord
     */
    public function getNeedMARCRecord()
    {
    	return $this->needMARCRecord;
    }
    /**
     * Setter for the needMARCRecord
     * 
     * @param mixed $value
     * 
     * @return OrderItem
     */
    public function setNeedMARCRecord($value)
    {
    	$this->needMARCRecord = $value;
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
    		$array['order'] = array('id' => $this->getOrder()->getId());
    		$attributes = array();
    		foreach($this->getProduct()->getAttributes() as $attr)
    		{
    			$typeId = $attr->getType()->getCode();
    			if(!isset($attributes[$typeId]))
    				$attributes[$typeId] = array();
    			$attributes[$typeId][] = $attr->getJson();
    		}
    		$array['product'] = array('id' => $this->getProduct()->getId(), 'title' => $this->getProduct()->getTitle(), 'attributes' => $attributes);
    	}
    	return parent::getJson($array, $reset);
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__loadDaoMap()
     */
    public function __loadDaoMap()
    {
        DaoMap::begin($this, 'ord_item');
        DaoMap::setManyToOne("order", "Order", 'ord_item_order');
        DaoMap::setManyToOne("product", "Product", 'ord_item_product');
        DaoMap::setIntType('unitPrice', 'double', '10,4', false, '0.0000');
        DaoMap::setIntType('qty');
        DaoMap::setIntType('totalPrice', 'double', '10,4', false, '0.0000');
        DaoMap::setBoolType('needMARCRecord');
        parent::__loadDaoMap();
    
        DaoMap::createIndex('qty');
        DaoMap::createIndex('unitPrice');
        DaoMap::createIndex('totalPrice');
        DaoMap::createIndex('needMARCRecord');
        DaoMap::commit();
    }
    /**
     * Getting the order by order no
     * 
     * @param string $orderNo The order no
     * 
     * @return Ambigous <NULL, unknown>
     */
    public static function create(Order $order, Product $product, $qty = 0 , $needMARCRecord = false, $unitPrice = '0.0000', $totalPrice = '0.0000')
    {
    	$items = self::getAllByCriteria('orderId = ? and productId = ?', array($order->getId(), $product->getId()), true, 1, 1);
    	if(count($items) === 0)
    		$item = new OrderItem();
    	else
    		$item = $items[0];
    	$item->setOrder($order)
    		->setProduct($product)
    		->setUnitPrice($unitPrice)
    		->setQty($item->getQty() + $qty)
    		->setTotalPrice($item->getTotalPrice() + $totalPrice)
    		->setNeedMARCRecord($needMARCRecord)
    		->save();
    	return $item;
    }
}