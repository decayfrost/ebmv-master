<?php
/**
 * SupplierInfo Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class SupplierInfo extends BaseEntityAbstract
{
    /**
     * The supplier of the product
     * 
     * @var Supplier
     */
    protected $supplier;
    /**
     * The type of the SupplierInfo
     * 
     * @var SupplierInfoType
     */
    protected $type;
    /**
     * The value of the SupplierInfo
     * 
     * @var string
     */
    private $value;
    /**
     * Getter for the supplier
     * 
     * @return Supplier
     */
    public function getSupplier()
    {
        $this->loadManyToOne('supplier');
        return $this->supplier;
    }
    /**
     * Setter for the productattribute type
     * 
     * @param ProductAttributeType $type The type of the product attribute
     * 
     * @return SupplierInfo
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;
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
     * Setter for the SupplierInfo
     * 
     * @param string $value The value
     * 
     * @return SupplierInfo
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    /**
     * Getter for the type
     * 
     * @return SupplierInfoType
     */
    public function getType()
    {
        $this->loadManyToOne('type');
        return $this->type;
    }
    /**
     * Setter for the type
     * 
     * @param SupplierInfoType $type The SupplierInfoType
     * 
     * @return SupplierInfo
     */
    public function setType(SupplierInfoType $type)
    {
        $this->type = $type;
        return $this;
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__loadDaoMap()
     */
    public function __loadDaoMap()
    {
        DaoMap::begin($this, 'sup_info');
        DaoMap::setManyToOne('supplier', 'Supplier');
        DaoMap::setManyToOne('type', 'SupplierInfoType');
        DaoMap::setStringType('value', 'varchar', '255');
        parent::__loadDaoMap();
        DaoMap::commit();
    }
}