<?php
/**
 * ProductAttribute Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductAttribute extends BaseEntityAbstract
{
    /**
     * The type of the attribute
     * 
     * @var ProductAttributeType
     */
    protected $type;
    /**
     * The attribute of the product
     * 
     * @var string
     */
    private $attribute;
    /**
     * The product this attribute is belonging to
     * 
     * @var Product
     */
    protected $product;
    /**
     * Getter for the productattribute type
     * 
     * @return ProductAttributeType
     */
    public function getType()
    {
        $this->loadManyToOne('type');
        return $this->type;
    }
    /**
     * Setter for the productattribute type
     * 
     * @param ProductAttributeType $type The type of the product attribute
     * 
     * @return ProductAttribute
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    /**
     * Getter for the attribute
     * 
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
    /**
     * Setter for the attribute
     * 
     * @param string $attribute The attribute
     * 
     * @return ProductAttribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
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
     * @return ProductAttribute
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
        	$array['type'] = $this->getType()->getJson();
        return parent::getJson($array, $reset);
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__loadDaoMap()
     */
    public function __loadDaoMap()
    {
        DaoMap::begin($this, 'pa');
        DaoMap::setManyToOne('product', 'Product');
        DaoMap::setStringType('attribute','varchar', 500);
        DaoMap::setManyToOne('type', 'ProductAttributeType');
        parent::__loadDaoMap();
        DaoMap::createIndex('attribute');
        DaoMap::commit();
    }
    /**
     * Enter description here...
     *
     * @param Product              $product
     * @param ProductAttributeType $type
     * @param unknown_type         $pageNumber
     * @param unknown_type         $pageSize
     * @param unknown_type         $orderByParams
     * 
     * @return unknown
     */
    public static function getAttributeForProductAndType(Product $product, ProductAttributeType $type, $activeOnly = true, $pageNumber = null, $pageSize = 30, $orderByParams = array(), &$stats = array())
    {
    	return self::getAllByCriteria("productId = ? AND typeId = ?", array($product->getId(), $type->getId()), $activeOnly, $pageNumber, $pageSize, $orderByParams, $stats);
    }
    /**
     * update the product attribute, when exsits; otherwise create one
     *
     * @param Product              $product   The product
     * @param ProductAttributeType $type      The product type
     * @param string               $attribute The attribute content
     * @return Ambigous <BaseEntity, BaseEntityAbstract>
     */
    public static function updateAttributeForProduct(Product $product, ProductAttributeType $type, $attribute)
    {
    	if(count($atts = self::getAttributeForProductAndType($product, $type, true, 1, 1)) === 0)
    		$attr = new ProductAttribute();
    	else
    		$attr = $atts[0];
    
    	$attr->setType($type)
	    	->setProduct($product)
	    	->setAttribute($attribute)
	    	->save();
    	return $attr;
    }
    /**
     * removing a product attributes by product and type code
     *
     * @param Product $product   The product we are trying to deleting the attributes from
     * @param array   $typeCodes The code of the type
     */
    public static function removeAttrsForProduct(Product $product, array $typeCodes)
    {
    	$types = ProductAttributeType::getTypesByCodes($typeCodes);
    	if(count($types) === 0)
    		return;
    	$typeIds = array();
    	foreach($types as $type)
    		$typeIds[] = $type->getId();
    	self::updateByCriteria('active = 0', "productId = ? AND typeId in (" . implode(', ', array_fill(0, count($typeIds), '?')) . ")", array_merge(array($product->getId()), $typeIds));
    }
}