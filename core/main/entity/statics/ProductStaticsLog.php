<?php
/**
 * ProductStaticsLog Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductStaticsLog extends BaseEntityAbstract
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
     * The product statics
     * 
     * @var ProductStatics
     */
    protected $statics;
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
     * Getter for statics
     *
     * @return ProductStatics
     */
    public function getStatics() 
    {
    	$this->loadManyToOne('statics');
        return $this->statics;
    }
    /**
     * Setter for statics
     *
     * @param ProductStatics $value The statics
     *
     * @return ProductStaticsLog
     */
    public function setStatics($value) 
    {
        $this->statics = $value;
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
    public static function create(Product $product, ProductStaticsType $type, Library $library, ProductStatics $statics, $value = 1)
    {
    	$class = get_called_class();
    	$obj = new $class();
    	return $obj->setProduct($product)
    		->setType($type)
    		->setStatics($statics)
    		->setLibrary($library)
    		->setValue($value)
    		->save();
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__loadDaoMap()
     */
    public function __loadDaoMap()
    {
        DaoMap::begin($this, 'pstatslog');
        DaoMap::setManyToOne('product', 'Product');
        DaoMap::setIntType('value','int', 100);
        DaoMap::setManyToOne('type', 'ProductStaticsType');
        DaoMap::setManyToOne('library', 'Library');
        DaoMap::setManyToOne('statics', 'ProductStatics');
        parent::__loadDaoMap();
        DaoMap::createIndex('value');
        DaoMap::commit();
    }
}