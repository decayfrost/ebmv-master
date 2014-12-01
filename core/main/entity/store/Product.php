<?php
/**
 * Product Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Product extends BaseEntityAbstract
{
	/**
	 * attributes - cached
	 * 
	 * @var array
	 */
	private $_attris;
    /**
     * The title of the book
     * 
     * @var string
     */
    private $title;
    /**
     * Supplier Unique Key string
     * 
     * @var string
     */
    private $suk = '';
	/**
	 * The categories that the products are belongin to 
	 * 
	 * @var multiple:Category
	 */
	protected $categorys;
	/**
	 * The attributes of the products
	 * 
	 * @var multiple:ProductAttribute
	 */
	protected $attributes;
	/**
	 * The languages of the book
	 * 
	 * @var multiple:Language
	 */
	protected $languages;
	/**
	 * The ProductType of the book
	 * 
	 * @var ProductType
	 */
	protected $productType;
	/**
	 * The ProductStatics of the book
	 * 
	 * @var ProductStatics
	 */
	protected $productStatics;
	/**
	 * The shelf items
	 * 
	 * @var multiple:ProductShelfItem
	 */
	protected $shelfItems;
	/**
	 * The supplier of this product
	 * 
	 * @var Supplier
	 */
	protected $supplier;
	/**
	 * The library of the products
	 * 
	 * @var LibraryOwns
	 */
	protected $libOwns;
	/**
	 * Getter for the title
	 * 
	 * @return string
	 */
	public function getTitle()
	{
	    return $this->title;
	}
	/**
	 * Setter for 
	 * 
	 * @param string $title The title of product
	 * 
	 * @return Product
	 */
	public function setTitle($title)
	{
	    $this->title = $title;
	    return $this;
	}
	/**
	 * Getter for the suk
	 * 
	 * @return string
	 */
	public function getSuk()
	{
	    return $this->suk;
	}
	/**
	 * Setter for suk
	 * 
	 * @param string $suk The suk of product
	 * 
	 * @return Product
	 */
	public function setSuk($suk)
	{
	    $this->suk = $suk;
	    return $this;
	}
	/**
	 * getter Categorys
	 *
	 * @return multiple:Category
	 */
	public function getCategorys()
	{
	    $this->loadManyToMany("categorys");
	    return $this->categorys;
	}
	/**
	 * Setter Categorys
	 *
	 * @param array $categorys The categories that the products are belongin to 
	 *
	 * @return Product
	 */
	public function setCategorys($categorys)
	{
	    $this->categorys = $categorys;
	    return $this;
	}
	/**
	 * Adding a product to a category
	 *
	 * @param Category $category The category
	 *
	 * @return Product
	 */
	public function addCategory(Category $category)
	{
		self::saveManyToManyJoin($category, $this);
		return $this;
	}
	/**
	 * Removing a product from a category
	 *
	 * @param Category $category The category
	 *
	 * @return Product
	 */
	public function removeCategory(Category $category)
	{
		self::deleteManyToManyJoin($category, $this);
		return $this;
	}
	/**
	 * Removing all the categories
	 */
	public function removeAllCategories()
	{
		foreach($this->getCategorys() as $category)
			$this->removeCategory($category);
		return $this;
	}
	/**
	 * getter attributes
	 *
	 * @return multiple:ProductAttribute
	 */
	public function getAttributes()
	{
	    $this->loadOneToMany('attributes');
	    return $this->attributes;
	}
	/**
	 * Setter attributes
	 *
	 * @param array $attributes The attributes that this product has
	 *
	 * @return Product
	 */
	public function setAttributes($attributes)
	{
	    $this->attributes = $attributes;
	    return $this;
	}
	/**
	 * Getting the attribute
	 * 
	 * @param string $typeCode  The code of the ProductAttributeType
	 * @param string $separator The separator of the returned attributes, in case there are multiple
	 * @param bool   $reset     Forcing to get the information from DB
	 * 
	 * @return Ambigous <>
	 */
	public function getAttribute($typeCode, $separator = ',', $reset = false)
	{
		if(!isset($this->_attris[$typeCode]) || $reset === true)
		{
			$sql = 'select group_concat(pa.attribute separator ?) `attr` from productattribute pa inner join productattributetype pat on (pat.id = pa.typeId and pat.active = 1 and pat.code = ?) where pa.active = 1 and pa.productId = ?';
		    $result = Dao::getSingleResultNative($sql, array($separator, $typeCode, $this->getId()), PDO::FETCH_ASSOC);
		    $this->_attris[$typeCode] = $result['attr'];
		}
	    return $this->_attris[$typeCode];
	}
	/**
	 * Getter for the language
	 * 
	 * @return Multiple:Language
	 */
	public function getLanguages()
	{
	    $this->loadManyToMany('languages');
	    return $this->languages;
	}
	/**
	 * Setter for the language
	 * 
	 * @param Language $language The language of the product
	 * 
	 * @return Product
	 */
	public function setLanguages(array $languages)
	{
	    $this->languages = $languages;
	    return $this;
	}
	/**
	 * updating the languages
	 * 
	 * @param array $languages The wanted languages
	 * 
	 * @return Product
	 */
	public function updateLanguages(array $languages)
	{
		if(count($languages) === 0)
			return;
		
		foreach($languages as $lang)
		{
			Product::replaceInto('language_product', array('languageId', 'productId', 'createdById'), array('?', $this->getId(), Core::getUser()->getId()), array($lang->getId()));
		}
		return $this;
	}
	/**
	 * Adding a library for owning this product
	 * 
	 * @param Library         $lib         The owner
	 * @param LibraryOwnsType $type        Which type does the library owns this product
	 * @param number          $availCopies How many copies
	 * @param number          $totalCopies How many copies in total
	 * 
	 * @return Product
	 */
	public function updateLibrary(Library $lib, LibraryOwnsType $type, $avail = 0, $total = 0)
	{
		$owns = $this->getLibraryOwn($lib, $type);
		if(count($owns) === 0)
			$owns = new LibraryOwns();
		else
		{
			$owns = $owns[0];
			$this->removeLibrary($lib, $type);
		}
		$owns->setLibrary($lib)
			->setProduct($this)
			->setType($type)
			->setAvail($avail)
			->setTotal($total)
			->setActive(true)
			->save();
		return $this;
	}
	/**
	 * Removing a product form a library
	 * 
	 * @param Library         $lib  The library
	 * @param LibraryOwnsType $type The ownership type
	 * 
	 * @return Product
	 */
	public function removeLibrary(Library $lib, LibraryOwnsType $type)
	{
		LibraryOwns::updateByCriteria('active = 0' , 'libraryId = ? and typeId = ? and productId = ?', array($lib->getId(), $type->getId(), $this->getId()));
		return $this;
	}
	/**
	 * Getting the library own for this product
	 * 
	 * @param Library         $lib  The owner
	 * @param LibraryOwnsType $type The ownership type
	 * 
	 * @return NULL|LibraryOwns
	 */
	public function getLibraryOwn(Library $lib, LibraryOwnsType $type = null, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), $activeOnly = true, &$stats = array())
	{
		$where = 'libraryId = ? and productId = ?';
		$params =  array($lib->getId(), $this->getId());
		if($type instanceof LibraryOwnsType)
		{
			$where .= ' and typeId = ?';
			$params[] = $type->getId();
		}
		$owns = LibraryOwns::getAllByCriteria($where, $params, $activeOnly, $pageNo, $pageSize, $orderBy, $stats);
		return $owns;
	}
	/**
	 * Getter for the ProductType
	 * 
	 * @return ProductType
	 */
	public function getProductType()
	{
	    $this->loadManyToOne('productType');
	    return $this->productType;
	}
	/**
	 * Setter for the productType
	 * 
	 * @param ProductType $productType The productType
	 * 
	 * @return Product
	 */
	public function setProductType(ProductType $productType)
	{
	    $this->productType = $productType;
	    return $this;
	}
	/**
	 * Getter for the ProductStatics
	 * 
	 * @return ProductStatics
	 */
	public function getProductStatics()
	{
	    $this->loadOneToMany('productStatics');
	    return $this->productStatics;
	}
	/**
	 * Setter for the ProductStatics
	 * 
	 * @param array $productStatics The array of ProductStatics
	 * 
	 * @return Product
	 */
	public function setProductStatics($productStatics)
	{
	    $this->productStatics = $productStatics;
	    return $this;
	}
	/**
	 * add statics to a product
	 * 
	 * @param Library            $lib
	 * @param ProductStaticsType $type
	 * @param number             $increaseBy
	 * @return Product
	 */
	public function addStatic(Library $lib, ProductStaticsType $type, $increaseBy = 1)
	{
		if(trim($this->getId()) === '')
			$this->save();
		ProductStatics::create($this, $type, $lib)->add($increaseBy);
		return $this;
	}
	/**
	 * get statics to a product
	 * 
	 * @param Library            $lib
	 * @param ProductStaticsType $type
	 * 
	 * @return ProductStatics
	 */
	public function getStatic(Library $lib, ProductStaticsType $type)
	{
		return ProductStatics::getStats($this, $type, $lib);
	}
	/**
	 * Getting the suppliers for this product
	 * 
	 * @return multitype:|Ambigous <multitype:, multitype:BaseEntityAbstract >
	 */
	public function getSuppliers()
	{
		return array($this->getSupplier());
	}
	/**
	 * Getting the supplier
	 * 
	 * @return Supplier
	 */
	public function getSupplier() 
	{
		$this->loadManyToOne('supplier');
	    return $this->supplier;
	}
	/**
	 * Setter for the supplier
	 * 
	 * @param Supplier $value The Supplier
	 * 
	 * @return Product
	 */
	public function setSupplier($value) 
	{
	    $this->supplier = $value;
	    return $this;
	}
	/**
	 * Getter for the shelfItems
	 * 
	 * @return multiple:ProductShelfItem
	 */
	public function getShelfItems() 
	{
		$this->loadOneToMany('shelfItems');
	    return $this->shelfItems;
	}
	/**
	 * Getters for the libOwns
	 * 
	 * @return Multiple:LibraryOwns
	 */
	public function getLibOwns() 
	{
		$this->loadOneToMany('libOwns');
	    return $this->libOwns;
	}
	/**
	 * Setter for the libOwns
	 * 
	 * @param multiple:libOwns $value The libOwns
	 * 
	 * @return Product
	 */
	public function setLibOwns($value) 
	{
	    $this->libOwns = $value;
	    return $this;
	}
	/**
	 * Setter for the shelfItems
	 * 
	 * @param array $value The shelf items
	 * 
	 * @return Product
	 */
	public function setShelfItems($value) 
	{
	    $this->shelfItems = $value;
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
	    	$array['attributes'] = array();
		    foreach($this->getAttributes() as $attr)
		    {
		        $typeId = $attr->getType()->getCode();
		        if(!isset($array['attributes'][$typeId]))
		            $array['attributes'][$typeId] = array();
	            $array['attributes'][$typeId][] = $attr->getJson();
		    }
		    $array['languages'] = array();
		    foreach($this->getLanguages() as $lang)
		    	$array['languages'][] = $lang->getJson();
	    }
	    return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::postSave()
	 */
	public function postSave()
	{
	    if(trim($this->getSuk()) === '')
	        $this->setSuk(self::formatSKU($this->getAttribute(ProductAttributeType::ID_ISBN), $this->getAttribute(ProductAttributeType::ID_CNO)));
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pro');
		DaoMap::setStringType('title','varchar', 200);
		DaoMap::setStringType('suk','varchar', 50);
		DaoMap::setManyToMany("categorys", "Category", DaoMap::LEFT_SIDE, "pcat");
		DaoMap::setOneToMany("attributes", "ProductAttribute");
		DaoMap::setManyToMany("languages", "Language", DaoMap::LEFT_SIDE, 'lang');
		DaoMap::setManyToOne("productType", "ProductType");
		DaoMap::setOneToMany("productStatics", "ProductStatics");
		DaoMap::setOneToMany("shelfItems", "ProductShelfItem");
		DaoMap::setManyToOne('supplier', 'Supplier');
		DaoMap::setOneToMany("libOwns", "LibraryOwns");
		parent::__loadDaoMap();
		
		DaoMap::createIndex('title');
		DaoMap::createIndex('suk');
		DaoMap::commit();
	}
	/**
	 * format the isbn + cno, as it's unique for a product
	 * 
	 * @param string $isbn The ISBN string
	 * @param string $cno  The CNO string
	 * 
	 * @return string
	 */
	public static function formatSKU($isbn, $cno)
	{
		return md5($isbn . '|' . $cno);
	}
	/**
	 * Searching any product which has that attributetype code and same attribute content
	 *
	 * @param string $code             The code of the attribute type
	 * @param string $attribute        The content of the attribute
	 * @param bool   $searchActiveOnly Whether we return the inactive products
	 * @param int    $pageNo           The page number
	 * @param int    $pageSize         The page size
	 * @param array  $orderBy          The order by clause
	 *
	 * @return array
	 */
	public static function findProductWithAttrCode($code, $attribute, $searchActiveOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array())
	{
		$query = Product::getQuery();
		$query->eagerLoad('Product.attributes', DaoQuery::DEFAULT_JOIN_TYPE, 'pa')->eagerLoad('ProductAttribute.type', DaoQuery::DEFAULT_JOIN_TYPE, 'pt');
		$where = array('pt.code = ? and pa.attribute = ?');
		$params = array($code, $attribute);
		return Product::getAllByCriteria(implode(' AND ', $where), $params, $searchActiveOnly, $pageNo, $pageSize, $orderBy);
	}
	/**
	 * Get the product with isbn and cno
	 *
	 * @param string   $isbn     The ISBN string
	 * @param string   $cno      The cno
	 * @param Supplier $supplier A supplier we are looking in
	 *
	 * @return Ambigous <NULL, BaseEntityAbstract>
	 */
	public static function findProductWithISBNnCno($isbn, $cno, Supplier $supplier = null)
	{
		$query = Product::getQuery();
		$query->eagerLoad('Product.attributes', DaoQuery::DEFAULT_JOIN_TYPE, 'pa')->eagerLoad('ProductAttribute.type', DaoQuery::DEFAULT_JOIN_TYPE, 'pt');
		$query->eagerLoad('Product.attributes', DaoQuery::DEFAULT_JOIN_TYPE, 'pa1')->eagerLoad('ProductAttribute.type', DaoQuery::DEFAULT_JOIN_TYPE, 'pt1', 'pa1.typeId = pt1.id');
		$where = array('pt.code = ? and pa.attribute = ? and pt1.code = ? and pa1.attribute = ?');
		$params = array('isbn', $isbn, 'cno', $cno);
		if($supplier instanceof Supplier)
		{
			$where[] = 'pro.supplierId = ?';
			$params[] = $supplier->getId();
		}
		$results = Product::getAllByCriteria(implode(' AND ', $where), $params, true, 1, 1);
		return count($results) > 0 ? $results[0] : null;
	}
	/**
	 * Searching the products in category
	 *
	 * @param Libraray $lib              The library  we are search in
	 * @param string   $searchText       The searching text
	 * @param array    $categorIds       the ids of the category
	 * @param bool     $searchActiveOnly Whether we return the inactive products
	 * @param int      $pageNo           The page number
	 * @param int      $pageSize         The page size
	 * @param array    $orderBy          The order by clause
	 *
	 * @return array
	 */
	public static function findProductsInCategory(Library $lib = null, $searchText = '', $categorIds = array(), $searchOption = '', Language $language = null, ProductType $productType = null, $searchActiveOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		$searchMode = false;
		$where = $params = array();
		$searchOption = trim($searchOption);
	
		$query = Product::getQuery();
		if($lib instanceof Library)
		{
			$query->eagerLoad('Product.libOwns', DaoQuery::DEFAULT_JOIN_TYPE, 'lib_own', 'lib_own.libraryId = ? and lib_own.productId = pro.id and lib_own.active = 1');
			$params[] = $lib->getId();
		}
		if(($searchText = trim($searchText)) !== '')
		{
			$query->eagerLoad('Product.attributes', DaoQuery::DEFAULT_JOIN_TYPE, 'pa')->eagerLoad('ProductAttribute.type', DaoQuery::DEFAULT_JOIN_TYPE, 'pt');
			if($searchOption === '')
			{
				$criteria = '(pt.searchable = ?';
				$params[] = 1;
			}
			else
			{
				$criteria = '(pt.code = ?';
				$params[] = $searchOption;
			}
			$where[] = $criteria.' and pa.attribute like ?) or pro.title like ?';
			$params[] = '%' . $searchText . '%';
			$params[] = '%' . $searchText . '%';
			$searchMode = true;
		}
		if($language instanceof Language)
		{
			$query->eagerLoad('Product.languages', DaoQuery::DEFAULT_JOIN_TYPE, 'lang');
			$where[] = 'lang.id = ?';
			$params[] = $language->getId();
			$searchMode = true;
		}
		if($productType instanceof ProductType)
		{
			$where[] = 'pro.productTypeId = ?';
			$params[] = $productType->getId();
			$searchMode = true;
		}
	
		if(count($categorIds = array_filter($categorIds)) > 0)
		{
			$query->eagerLoad('Product.categorys');
			$where[] = '(pcat.id IN (' . implode(', ', array_fill(0, count($categorIds), '?')) . '))';
			$params = array_merge($params, $categorIds);
			$searchMode = true;
		}
	
		if($searchMode === false)
			return self::getAll($searchActiveOnly, $pageNo, $pageSize, $orderBy, $stats);
		return self::getAllByCriteria(implode(' AND ', $where), $params, $searchActiveOnly, $pageNo, $pageSize, $orderBy, $stats);
	}
	/**
	 * Getting the product by SKU
	 * 
	 * @param string $sku The sku of the product
	 * 
	 * @return NULL|Product
	 */
	public static function getProductBySKU($sku)
	{
		$products = self::getAllByCriteria('suk=?', array(trim($sku)), true, 1, 1);
		return (count($products) > 0 ? $products[0] : null);
	}
	/**
	 * Create a product
	 *
	 * @param string      $sku        The sku of the product
	 * @param string      $title      The title of the product
	 * @param ProductType $type       The product type object
	 * @param Supplier    $supplier   The supplier object
	 * @param array       $categories The categories of the product
	 * @param array       $langs      The array of language objects
	 * @param array       $info       The array of product attributes array('typecode' => array('attribute value', 'attribute_value2'))
	 *
	 * @return Product
	 */
	public static function createProduct($sku, $title, ProductType $type, Supplier $supplier, array $categories, array $langs, array $info = array())
	{
		$product = ($product = self::getProductBySKU($sku)) instanceof Product ? $product : new Product();
		return self::_editProduct($product, $title, $type, $supplier, $categories, $langs, $info, $sku);
	}
	/**
	 * update a product
	 *
	 * @param string      $title      The title of the product
	 * @param ProductType $type       The product type object
	 * @param Supplier    $supplier   The supplier object
	 * @param array       $categories The categories of the product
	 * @param array       $langs      The array of language objects
	 * @param array       $info       The array of product attributes array('typecode' => array('attribute value', 'attribute_value2'))
	 * @param string      $title      The sku of the product
	 *
	 * @return Product
	 */
	public static function updateProduct(Product $product, $title, ProductType $type, Supplier $supplier, array $categories, array $langs, array $info = array(), $sku = '')
	{
		return self::_editProduct($product, $title, $type, $supplier, $categories, $langs, $info, $sku);
	}
	/**
	 * editing a product
	 *
	 * @param string      $title      The title of the product
	 * @param ProductType $type       The product type object
	 * @param Supplier    $supplier   The supplier object
	 * @param array       $categories The categories of the product
	 * @param array       $langs      The array of language objects
	 * @param array       $info       The array of product attributes array('typecode' => array('attribute value', 'attribute_value2'))
	 * @param string      $title      The sku of the product
	 *
	 * @return Product
	 */
	private static function _editProduct(Product &$product, $title, ProductType $type, Supplier $supplier, array $categories, array $langs, array $info = array(), $sku = '')
	{
		//setting up the product object
		$product->setTitle($title)
			->setProductType($type)
			->setSupplier($supplier);
		if(trim($sku) !== '')
			$product->setSuk($sku);
		$product->save();

		//setup the languages
		$langs = array_filter($langs, create_function('$a', 'return ($a instanceof Language);'));
		if(count($langs) === 0 )
			throw new CoreException('At least one lanugage needed!');
		$product->updateLanguages($langs);

		//add the attributes
		if(count($info) > 0)
		{
			//TODO:: need to resize the thumbnail
			$typeCodes = array_keys($info);
			$types = ProductAttributeType::getTypesByCodes($typeCodes);
			foreach($typeCodes as $typeCode)
			{
				if(!isset($types[$typeCode]) || !$types[$typeCode] instanceof ProductAttributeType)
					throw new CoreException('Could find the typecode for: ' . $typeCode);
				foreach($info[$typeCode] as $attr)
				{
					if(($attr = trim($attr)) === '')
						continue;
					ProductAttribute::updateAttributeForProduct($product, $types[$typeCode], $attr);
				}
			}
		}

		//add categories
		foreach($categories as $category)
		{
			if(!$category instanceof Category)
				continue;
			$product->addCategory($category);
		}
		return $product;
	}
	/**
	 * Update the product attributes from _editProduct() function
	 *
	 * @param Product              $product   The product
	 * @param ProductAttributeType $type      The product type
	 * @param string               $attribute The attribute content
	 *
	 * @return Product
	 */
	private static function _updateAttribute(Product &$product, ProductAttributeType $type = null, $attribute = "")
	{
		if($type instanceof ProductAttributeType || ($attribute = trim($attribute)) === "")
			return $product;
		return ProductAttribute::updateAttributeForProduct($product, $type, $attribute);
	}
	/**
	 * Getting the Most popular products
	 *
	 * @param Library $lib   The library we are view now
	 * @param int     $limit How many we are getting
	 *
	 * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getMostPopularProducts(Library $lib, Language $lang = null, ProductType $type = null, $pageNo = 1, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array('pstats.value'=>'desc'), &$stats = array())
	{
		$query = Product::getQuery();
		$query->eagerLoad('Product.libOwns', DaoQuery::DEFAULT_JOIN_TYPE, 'lib_own', 'lib_own.libraryId = ? and lib_own.productId = pro.id and lib_own.active = 1')->eagerLoad('Product.productStatics', 'left join', 'pstats')->eagerLoad('ProductStatics.type', 'left join', 'pstatstype');
		$where = 'pstatstype.code = ? or pstatstype.code is null';
		$params = array($lib->getId(), 'no_of_clicks');
		if($lang instanceof Language)
		{
			$query->eagerLoad('Product.languages');
			$where .= ' AND lang.id = ?';
			$params[] = trim($lang->getId());
		}
		if($type instanceof ProductType)
		{
			$where .= ' AND pro.productTypeId = ?';
			$params[] = trim($type->getId());
		}
		$results = self::getAllByCriteria($where ,$params, true, $pageNo, $pageSize, $orderBy, $stats);
		return $results;
	}
	/**
	 * Getting the lastest products
	 *
	 * @param Library $lib   The library we are view now
	 * @param int     $limit How many we are getting
	 *
	 * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getNewReleasedProducts(Library $lib, Language $lang = null, ProductType $type = null, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array('pro.id'=>'desc'), &$stats = array())
	{
		$query = self::getQuery();
		$query->eagerLoad('Product.libOwns', DaoQuery::DEFAULT_JOIN_TYPE, 'lib_own', 'lib_own.productId = pro.id and lib_own.active = 1');
		$where = 'lib_own.libraryId = ?';
		$params = array($lib->getId());
		if($lang instanceof Language)
		{
			$query->eagerLoad('Product.languages');
			$where .= ' AND lang.id = ?';
			$params[] = trim($lang->getId());
		}
		if($type instanceof ProductType)
		{
			$where .= ' AND pro.productTypeId = ?';
			$params[] = trim($type->getId());
		}
		$results =  self::getAllByCriteria($where, $params, true, $pageNo, $pageSize, array('pro.id'=>'desc'), $stats);
		return $results;
	}
	/**
	 * Getting the products that on the bookshelf
	 *
	 * @param UserAccount $user     The owner of the bookshelf
	 * @param int         $pageNo   The pageNumber
	 * @param int         $pageSize The pageSize
	 * @param array       $orderBy  The order by clause
	 *
	 * @return multitype:|Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getShelfProducts(UserAccount $user, Supplier $supplier = null, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		$query = self::getQuery();
		$where = 'shelf_item.ownerId = ? and shelf_item.active = ?';
		$params = array($user->getId(), 1);
		if($supplier instanceof Supplier)
		{
			$where .= ' AND pro.supplierId = ?';
			$params[] = $supplier->getId();
		}
		$query->eagerLoad('Product.shelfItems', DaoQuery::DEFAULT_JOIN_TYPE, 'shelf_item');
		$result = self::getAllByCriteria($where, $params, true, $pageNo, $pageSize, $orderBy, $stats);
		return $result;
	}
}

?>