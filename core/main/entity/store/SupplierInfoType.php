<?php
/**
 * SupplierInfoType Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class SupplierInfoType extends BaseEntityAbstract
{
	const ID_IMAGE_LOCATION = 6;
    /**
     * The attribute of the product
     * 
     * @var string
     */
    private $name;
    /**
     * The unique code for this type
     * 
     * @var string
     */
    private $code;
    /**
     * Getter for the name of the type
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Setter for the name fo the type
     * 
     * @param string $name The name
     * 
     * @return ProductAttributeType
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @param string $code The code of the type
     * 
     * @return ProductAttributeType
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntityAbstract::__toString()
     */
    public function __toString()
    {
        return $this->name;
    }
    /**
    * (non-PHPdoc)
    * @see BaseEntity::__loadDaoMap()
    */
    public function __loadDaoMap()
    {
        DaoMap::begin($this, 'sup_info_type');
        DaoMap::setStringType('name','varchar', 50);
        DaoMap::setStringType('code','varchar', 50);
        parent::__loadDaoMap();
        DaoMap::createIndex('name');
        DaoMap::createUniqueIndex('code');
        DaoMap::commit();
    }
}