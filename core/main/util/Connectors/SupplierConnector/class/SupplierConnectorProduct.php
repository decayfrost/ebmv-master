<?php
/**
 * Supplier connector product is a fake product for containing the data from supplier about a product
 *
 * @package    Core
 * @subpackage Utils
 * @author     lhe<helin16@gmail.com>
 */
class SupplierConnectorProduct
{
	private static $_products = array();
	private $_title;
	private $_isbn;
	private $_cno;
	private $_author;
	private $_publisher;
	private $_publish_date;
	private $_no_of_words;
	private $_image_thumb;
	private $_description;
	private $_cip;
	private $_libCode;
	private $_languageCodes;
	private $_categoryNames;
	private $_productTypeName;
	private $_price;
	private $_copies = array('onlineRead' => array ('avail' => 0, 'total' => 0), 'download' => array ('avail' => 0, 'total' => 0));
	/**
	 * Getting the SupplierConnectorProduct
	 * 
	 * @param SimpleXMLElement $productinfo The product
	 * 
	 * @return Ambigous <arary, multitype:string multitype: NULL multitype:multitype:string   multitype:multitype:string  multitype:NULL  multitype:unknown  multitype:Ambigous <number, string>   >|Ambigous <arary, multitype:unknown string multitype: multitype:multitype:string   mixed multitype:multitype:unknown  multitype:string  multitype:Ambigous <number, string>   >
	 */
	public static function getProduct(SimpleXMLElement $productinfo)
	{
		$key = md5($productinfo->asXML());
		if(!isset(self::$_products[$key]))
			
			self::$_products[$key] = new SupplierConnectorProduct(
				trim(self::_getAttribute($productinfo, 'BookName')), 
				trim(self::_getAttribute($productinfo, 'Isbn')), 
				trim(self::_getAttribute($productinfo, 'NO')), 
				trim(self::_getAttribute($productinfo, 'Author')), 
				trim(self::_getAttribute($productinfo, 'Press')), 
				trim(self::_getAttribute($productinfo, 'PublicationDate')), 
				trim(self::_getAttribute($productinfo, 'Words')), 
				trim(self::_getAttribute($productinfo, 'FrontCover')), 
				trim(self::_getAttribute($productinfo, 'Introduction')), 
				trim(self::_getAttribute($productinfo, 'Cip')), 
				trim(self::_getAttribute($productinfo, 'SiteID')), 
				preg_split('/[+\/]/', trim(self::_getAttribute($productinfo, 'Language'))), 
				explode('/', self::_getAttribute($productinfo, 'BookType')), 
				strtolower(trim($productinfo->getName())), 
				self::_getCopies($productinfo),
				trim(self::_getAttribute($productinfo, 'Price')) 
			);
		return self::$_products[$key];
	}
	/**
	 * Getting the initial pagination details for function SupplierConn::getProductListInfo()
	 * 
	 * @param SimpleXMLElement $listXml The listing xml objects
	 * 
	 * @return multitype:number string
	 */
	public static function getInitPagination(SimpleXMLElement $listXml = null, $totalRecords = 0 , $pageNo = 1, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE)
	{
		$array = array('totalPages' => (intval($pageSize) === 0 ? 0 : ceil($totalRecords / $pageSize)), 'pageNo' => $pageNo, 'pageSize' => $pageSize, 'totalRecords' => $totalRecords);
		if($listXml instanceof SimpleXMLElement)
		{
			foreach($listXml->attributes() as $key => $value)
				$array[$key] = trim($value);
		}
		return $array;
	}
	/**
	 * Getting the copies information
	 * 
	 * @param SimpleXMLElement $productinfo
	 * 
	 * @return multitype:|multitype:multitype:string
	 */
	private static function _getCopies(SimpleXMLElement $productinfo)
	{
		$copies = array();
		foreach(LibraryOwnsType::getAll() as $type)
		{
			$copyStats = array('avail' => 0,  'total' => 0);
			$code = trim($type->getCode());
			if(isset($productinfo->Copies) && isset($productinfo->Copies->$code))
			{
				$copyElementXml = $productinfo->Copies->$code;
				$copyStats['avail'] = trim(self::_getAttribute($copyElementXml, 'Available', 0));
				$copyStats['total'] = trim(self::_getAttribute($copyElementXml, 'Total', 0));
			}
			$copies[trim($type->getId())] = $copyStats;
		}
		return $copies;
	}
	/**
	 * constructor
	 * 
	 * @param string $title
	 * @param string $isbn
	 * @param string $cno
	 * @param string $author
	 * @param string $publisher
	 * @param string $publish_date
	 * @param string $no_of_words
	 * @param string $image_thumb
	 * @param string $description
	 * @param string $cip
	 * @param string $libCode
	 * @param string $languageCodes
	 * @param string $categoryNames
	 * @param string $productTypeName
	 * @param array  $copies
	 */
	public function __construct($title, $isbn, $cno, $author, $publisher, $publish_date, $no_of_words, $image_thumb, $description, $cip, $libCode, $languageCodes, $categoryNames, $productTypeName, array $copies, $price = '0.0000')
	{
		if(trim($isbn) === '')
			throw new SupplierConnectorException('No ISBN provided!');
		if(trim($cno) === '')
			$cno = 0;
		$this->_title = $title;
		$this->_isbn = $isbn;
		$this->_cno = $cno;
		$this->_author = $author;
		$this->_publisher = $publisher;
		$this->_publish_date = $publish_date;
		$this->_no_of_words = $no_of_words;
		$this->_image_thumb = $image_thumb;
		$this->_description = $description;
		$this->_cip = $cip;
		$this->_libCode = $libCode;
		$this->_languageCodes = $languageCodes;
		$this->_categoryNames = $categoryNames;
		$this->_productTypeName = $productTypeName;
		$this->_copies = $copies;
		$this->_price = $price;
	}
	/**
	 * Getting the value of the attribute
	 *
	 * @param SimpleXMLElement $xml           The xml element
	 * @param string           $attributeName The attr name
	 * @param string           $defaultValue  The default value
	 *
	 * @return string
	 */
	private static function _getAttribute(SimpleXMLElement $xml, $attributeName, $defaultValue = '')
	{
		return (isset($xml->$attributeName) && ($attribute = trim($xml->$attributeName)) !== '') ? $attribute : $defaultValue;
	}
	/**
	 * Getting the array of object
	 * 
	 * @return multitype:string NULL multitype:multitype:string  multitype:NULL   multitype:multitype:number
	 */
	public function getArray()
	{
		return array(
			'title' => trim($this->_title)
			, 'attributes' => array(
					'isbn' => array($this->_isbn)
					, 'cno' => array($this->_cno)
					, 'author' => array($this->_author)
					, 'publisher' => array($this->_publisher)
					, 'publish_date' => array($this->_publish_date)
					, 'no_of_words' => array($this->_no_of_words)
					, 'image_thumb' => array(trim($this->_image_thumb))
					, 'description' => array(trim($this->_description))
					, 'price' =>  array(trim($this->_price))
			)
			, 'libCode' => $this->_libCode
			, 'languageCodes' => $this->_languageCodes
			, 'categories' => $this->_categoryNames
			, 'productTypeName' => $this->_productTypeName
			, 'copies' => $this->_copies
		);
	}
}

