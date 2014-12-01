<?php
/**
 * ProductStatics Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductStatics extends BaseEntityAbstract
{
    /**
     * The type of the value
     * 
     * @var ProductStaticsType
     */
    protected $type;
    /**
     * The value of the product
     * 
     * @var string
     */
    private $value;
    /**
     * The product this value is belonging to
     * 
     * @var Product
     */
    protected $product;
    /**
     * The library this value is for
     * 
     * @var Library
     */
    protected $library;
    /**
     * Getter for the ProductStatics type
     * 
     * @return ProductStaticsType
     */
    public function getType()
    {
        $this->loadManyToOne('type');
        return $this->type;
    }
    /**
     * Setter for the ProductStatics type
     * 
     * @param ProductStaticsType $type The type of the product value
     * 
     * @return ProductStatics
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * Setter for the Library
     * 
     * @param Library $library The library of the product value
     * 
     * @return ProductStatics
     */
    public function setLibrary(Library $library)
    {
        $this->library = $library;
        return $this;
    }
    /**
     * Getter for the value
     * 
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
    /**
     * Setter for the value
     * 
     * @param string $value The value
     * 
     * @return ProductStatics
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    /**
     * Getter for the product
     * 
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }
    /**
     * Setter for the product
     * 
     * @param Product $product The product
     * 
     * @return ProductStatics
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }
    /**
     * Creating a object for ProductStatics
     * 
     * @param Product            $product
     * @param ProductStaticsType $type
     * @param Library            $library
     * @return Ambigous <GenericDAO, BaseEntityAbstract>
     */
    public static function create(Product $product, ProductStaticsType $type, Library $library, $value = 0)
    {
    	$class = get_called_class();
    	$obj = (($obj = self::getStats($product, $type, $library)) instanceof $class ? $obj : new $class());
    	$obj->setProduct($product)
    		->setType($type)
    		->setLibrary($library);
    	if(trim($obj->getId()) === '')
    		$obj->setValue($value);
    	return $obj->save();
    }
    /**
     * geting the stats
     * 
     * @param Product            $product
     * @param ProductStaticsType $type
     * @param Library            $library
     * @return Ambigous <GenericDAO, BaseEntityAbstract>
     */
    public static function getStats(Product $product, ProductStaticsType $type, Library $library)
    {
    	$objects = self::getAllByCriteria('productId = ? and typeId = ? and libraryId = ?', array($product->getId(), $type->getId(), $library->getId()),true, 1, 1);
    	return (count($objects) > 0 ? $objects[0] : null);
    }
    /**
     * increasing the value of the statics
     * 
     * @param number $increase
     * 
     * @return ProductStatics
     */
    public function add($increase = 1)
    {
    	ProductStaticsLog::create($this->getProduct(), $this->getType(), $this->getLibrary(), $this, $increase);
    	return $this->setValue($this->getValue() + $increase)
    		->save();
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
        DaoMap::begin($this, 'pstats');
        DaoMap::setManyToOne('product', 'Product');
        DaoMap::setIntType('value','int', 100);
        DaoMap::setManyToOne('type', 'ProductStaticsType');
        DaoMap::setManyToOne('library', 'Library');
        parent::__loadDaoMap();
        DaoMap::createIndex('value');
        DaoMap::commit();
    }
}