<?php
/**
 * Supplier connector interface
 *
 * @package    Core
 * @subpackage Utils
 * @author     lhe<helin16@gmail.com>
 */
interface SupplierConn
{
	/**
	 * Gettht product List
	 *
	 * @throws CoreException
	 * @return SimpleXMLElement
	 */
	public function getProductListInfo(ProductType $type = null);
	/**
	 * Getting xml product list
	 *
	 * @param number      $pageNo   The page no
	 * @param number      $pageSize the page size
	 * @param ProductType $type     The product type we are fetching
	 * @param bool        $onceOnly How many times we have to run the fetching script
	 *
	 * @return array The list which can be used in ImportProduct()
	 */
	public function getProductList($pageNo = 1, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, ProductType $type = null, $onceOnly = false);
	/**
	 * importing the products from the supplier
	 *
	 * @param string $productList The list of product from supplier
	 * @param int    $index       Which product of the file to import
	 *
	 * @throws CoreException
	 * @return array
	 */
	public function importProducts($productList, $index = null);
	/**
	 * Getting the book shelf
	 *
	 * @param UserAccount $user The current user
	 *
	 * @return Multiple:SupplierConnectorProduct
	 */
	public function getBookShelfList(UserAccount $user);
	/**
	 * Synchronize user's bookshelf from supplier to local
	 *
	 * @param UserAccount $user       The library current user
	 * @param array       $shelfItems The
	 *
	 * @return SupplierConnector
	 */
	public function syncUserBookShelf(UserAccount $user, array $shelfItems);
	/**
	 * Adding a product to the user's bookshelf
	 *
	 * @param UserAccount $user    The library current user
	 * @param Product     $product The product to be added
	 *
	 * @throws CoreException
	 * @return Ambigous <NULL, SimpleXMLElement>
	 */
	public function addToBookShelfList(UserAccount $user, Product $product);
	/**
	 * Removing a product from the book shelf
	 *
	 * @param UserAccount $user    The library current user
	 * @param Product     $product The product to be removed
	 *
	 * @throws CoreException
	 * @return mixed
	 */
	public function removeBookShelfList(UserAccount $user, Product $product);
	/**
	 * Getting the download url for a book
	 *
	 * @param Product     $product The product we are trying to get the url for
	 * @param UserAccount $user    Who wants to download it
	 *
	 * @throws Exception
	 * @return string
	 */
	public function getDownloadUrl(Product $product, UserAccount $user);
	/**
	 * Getting the online read url for a book
	 *
	 * @param Product     $product The product we are trying to get the url for
	 * @param UserAccount $user    Who wants to download it
	 *
	 * @throws Exception
	 * @return string
	 */
	public function getOnlineReadUrl(Product $product, UserAccount $user);
	/**
	 * Updating a product from supplier
	 * 
	 * @param Product $product The product that we are trying to update
	 * 
	 * @return SupplierConnectorAbstract
	 */
	public function updateProduct(Product &$product);
	/**
	 * Borrowing a product by a user
	 * 
	 * @param Product     $product The product that we are trying to update
	 * @param UserAccount $user    Who is borrowing the product
	 * 
	 * @return SupplierConnectorAbstract
	 */
	public function borrowProduct(Product &$product, UserAccount $user);
	/**
	 * Returning a product by a user
	 * 
	 * @param Product     $product The product that we are trying to update
	 * @param UserAccount $user    Who is borrowing the product
	 * 
	 * @return SupplierConnectorAbstract
	 */
	public function returnProduct(Product &$product, UserAccount $user);
	/**
	 * Getting a single product information from the supplier
	 * 
	 * @param string $isbn The isbn number
	 * @param string $no   The supplier unique number
	 * 
	 * @return SupplierConnectorProduct
	 */
	public function getProduct(Product $product);
}