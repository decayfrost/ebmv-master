<?php
/**
 * LibraryInfo Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class LibraryInfo extends BaseEntityAbstract
{
    /**
     * The Library of the product
     * 
     * @var Library
     */
    protected $library;
    /**
     * The type of the LibraryInfo
     * 
     * @var LibraryInfoType
     */
    protected $type;
    /**
     * The value of the LibraryInfo
     * 
     * @var string
     */
    private $value;
    /**
     * Getter for the Library
     * 
     * @return Library
     */
    public function getLibrary()
    {
        $this->loadManyToOne('library');
        return $this->library;
    }
    /**
     * Setter for the productattribute type
     * 
     * @param ProductAttributeType $type The type of the product attribute
     * 
     * @return LibraryInfo
     */
    public function setLibrary($Library)
    {
        $this->library = $Library;
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
     * Setter for the LibraryInfo
     * 
     * @param string $value The value
     * 
     * @return LibraryInfo
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    /**
     * Getter for the type
     * 
     * @return LibraryInfoType
     */
    public function getType()
    {
        $this->loadManyToOne('type');
        return $this->type;
    }
    /**
     * Setter for the type
     * 
     * @param LibraryInfoType $type The LibraryInfoType
     * 
     * @return LibraryInfo
     */
    public function setType(LibraryInfoType $type)
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
        DaoMap::begin($this, 'lib_info');
        DaoMap::setManyToOne('library', 'Library');
        DaoMap::setManyToOne('type', 'LibraryInfoType');
        DaoMap::setStringType('value', 'varchar', '255');
        parent::__loadDaoMap();
        DaoMap::commit();
    }
}