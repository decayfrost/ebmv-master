<?php
/**
 * Category Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Category extends TreeEntityAbstract
{
    /**
     * The name of the category
     * 
     * @var string
     */
    private $name;
    /**
     * The products that the products are belongin to
     *
     * @var multiple:Product
     */
    protected $products;
	/**
     * getter Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * setter Name
     *
     * @param string $Name The name of the role
     *
     * @return Category
     */
    public function setName($Name)
    {
        $this->name = $Name;
        return $this;
    }
    /**
     * Getting the products
     * 
     * @return multiple:Product
     */
    public function getProducts()
    {
        $this->loadManyToMany('products');
        return $this->products;
    }
    /**
     * setter for products
     *
     * @param string $Name The name of the role
     *
     * @return Category
     */
    public function setProducts($products)
    {
        $this->products = $products;
        return $this;
    }
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pcat');
		DaoMap::setStringType('name', 'varchar', 255);
		DaoMap::setManyToMany("products", "Product", DaoMap::RIGHT_SIDE, "pro", false);
		parent::__loadDaoMap();
		
		DaoMap::createIndex('name');
		DaoMap::commit();
	}
	/**
	 * Getting the categories for the language and type
	 *
	 * @param Language    $lang
	 * @param ProductType $type
	 * @param string      $searchActiveOnly
	 * @param int         $pageNo
	 * @param int         $pageSize
	 * @param array       $orderBy
	 *
	 * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getCategories(ProductType $type, Library $lib = null, Language $lang = null, $searchActiveOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		$query = self::getQuery();
		$query->eagerLoad('Category.products')->eagerLoad('Product.languages');
		$params = array();
		if($lib instanceof Library)
		{
			$query->eagerLoad('Product.libOwns', 'inner join', 'x_libowns', '`x_libowns`.`productId` = `pro`.id and x_libowns.active = 1 and x_libowns.libraryId = :libId');
			$params['libId'] =  $lib->getId();
		}
		$params['languageId'] =  $lang->getId();
		$params['productTypeId'] =  $type->getId();
		return self::getAllByCriteria('lang.id = :languageId and `pro`.productTypeId = :productTypeId', $params, $searchActiveOnly, $pageNo, $pageSize, $orderBy, $stats);
	}
	/**
	 * Find or create a category with the same name
	 *
	 * @param string   $categoryName The name of the category
	 * @param Category $parent       The parent category
	 * @param bool     $isNew        Whether we create a new category for this
	 *
	 * @return Category
	 */
	public static function updateCategory($categoryName, Category $parent = null, &$isNew = false)
	{
		$category = self::getAllByCriteria('name = ?', array($categoryName), true, 1, 1);
		if(count($category) > 0)
		{
			$isNew = false;
			return $category[0];
		}
	
		$isNew = true;
		$category = new Category();
		$category->setName($categoryName);
		return self::moveCategory($category, $parent);
	}
	/**
	 * move the category to another
	 *
	 * @param Category $category The moving category
	 * @param Caregory $parent   The target category
	 *
	 * @return Category
	 */
	public static function moveCategory(Category &$category, Category $parent = null)
	{
		if(($pos = trim($category->getPosition())) === '' || $pos === '1')
			$category->save();

		if($parent instanceof Category)
		{
			$newPos = $parent->getNextPosition();
			self::updateByCriteria('position = CONCAT(:newPos, substring(position, :posLen)), rootId = :newRootId', 'rootId = :rootId and position like :oldPos',
					array(
							'newPos' => $newPos,
							'oldPos' => $pos,
							'posLen' => strlen($pos) + 1,
							'newRootId' => $parent->getRoot()->getId(),
							'rootId' => $category->getRoot()->getId()
					)
			);
		}
		else
		{
			$newPos = '1';
			self::updateByCriteria('position = CONCAT(:newPos, substring(position, :posLen)), rootId = :newRootId', 'rootId = :rootId and position like :oldPos',
					array(
							'newPos' => $newPos,
							'oldPos' => $pos,
							'posLen' => strlen($pos) + 1,
							'newRootId' => $category->getId(),
							'rootId' => $category->getRoot()->getId()
					)
			);
		}

		$category = self::get($category->getId());
		$category->setPosition($newPos)
			->setParent($parent)
			->setRoot($parent instanceof Category ? $parent->getRoot() : $category)
			->save();
		return $category;
	
	}
	/**
	 * Searching the categories based on the product searching
	 * 
	 * @param unknown $productSearchString
	 * @param Library $lib
	 * @param string $searchActiveOnly
	 * @param string $pageNo
	 * @param unknown $pageSize
	 * @param unknown $orderBy
	 * 
	 * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	public static function searchCategoryByProduct($productSearchString, Library $lib = null, $searchActiveOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		$query = self::getQuery();
		$query->eagerLoad('Category.products');
		$params = array();
		if($lib instanceof Library)
		{
			$query->eagerLoad('Product.libOwns', 'inner join', 'x_libowns', '`x_libowns`.`productId` = `pro`.id and x_libowns.active = 1 and x_libowns.libraryId = :libId');
			$params['libId'] =  $lib->getId();
		}
		$where = 'pro.title like :searchTxt';
		$params['searchTxt'] = '%' . $productSearchString . '%';
		return self::getAllByCriteria($where, $params, $searchActiveOnly, $pageNo, $pageSize, $orderBy, $stats);
	}
}

?>