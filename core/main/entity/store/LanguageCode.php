<?php
/**
 * LanguageCode Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class LanguageCode extends BaseEntityAbstract
{
	private static $_cache;
    /**
     * The code of the language
     * 
     * @var string
     */
    private $code;
    /**
     * The Language
     * 
     * @var Language
     */
    protected $language;
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
     * setter for the code
     * 
     * @param string $value The value of the code
     * 
     * @return LanguageCode
     */
    public function setCode($value) 
    {
        $this->code = $value;
        return $this;
    }
    /**
     * Getter for the language
     * 
     * @return Language
     */
    public function getLanguage() 
    {
    	$this->loadManyToOne('language');
        return $this->language;
    }
    /**
     * Setter for the language
     * 
     * @param Language $value The language
     * 
     * @return LanguageCode
     */
    public function setLanguage(Language$value) 
    {
        $this->language = $value;
        return $this;
    }
    /**
     * getting the language code from language
     * 
     * @param Language $lang
     * 
     * @return Ambigous <multitype:, multitype:BaseEntityAbstract >
     */
    public static function getLanguageCodes(Language $lang)
    {
    	if(!isset(self::$_cache[$lang->getId()]))
    		self::$_cache[$lang->getId()] = self::getAllByCriteria('languageId = ?', array($lang->getId()));
    	return self::$_cache[$lang->getId()];
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__loadDaoMap()
     */
    public function __loadDaoMap()
    {
        DaoMap::begin($this, 'lcode');
        DaoMap::setStringType('code','varchar', 50);
        DaoMap::setManyToOne("language", "Language");
        parent::__loadDaoMap();
    
        DaoMap::createUniqueIndex('code');
        DaoMap::commit();
    }
}