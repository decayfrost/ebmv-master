<?php
/**
 * UserAccount Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class UserAccount extends BaseEntityAbstract
{
    /**
     * The id of the GUEST account
     * 
     * @var int
     */
    const ID_GUEST_ACCOUNT = 1;
    /**
     * The id of the system account
     * 
     * @var int
     */
    const ID_SYSTEM_ACCOUNT = 100;
    /**
     * The username
     *
     * @var string
     */
    private $username;
    /**
     * The password
     *
     * @var string
     */
    private $password;
    /**
     * The person
     *
     * @var Person
     */
    protected $person;
    /**
     * The roles that this person has
     *
     * @var array
     */
    protected $roles;
    /**
     * The library the user is belonging to
     * 
     * @var Library
     */
    protected $library;
    /**
     * getter UserName
     *
     * @return String
     */
    public function getUserName()
    {
        return $this->username;
    }
    /**
     * Setter UserName
     *
     * @param String $UserName The username
     *
     * @return UserAccount
     */
    public function setUserName($UserName)
    {
        $this->username = $UserName;
        return $this;
    }
    /**
     * getter Password
     *
     * @return String
     */
    public function getPassword()
    {
        return $this->password;
    }
    /**
     * Setter Password
     *
     * @param string $Password The password
     *
     * @return UserAccount
     */
    public function setPassword($Password)
    {
        $this->password = $Password;
        return $this;
    }
    /**
     * getter Person
     *
     * @return Person
     */
    public function getPerson()
    {
        $this->loadManyToOne("person");
        return $this->person;
    }
    /**
     * Setter Person
     *
     * @param Person $Person The person that this useraccount belongs to
     *
     * @return UserAccount
     */
    public function setPerson(Person $Person)
    {
        $this->person = $Person;
        return $this;
    }
    /**
     * getter Roles
     *
     * @return Roles
     */
    public function getRoles()
    {
        $this->loadManyToMany("roles");
        return $this->roles;
    }
    /**
     * setter Roles
     *
     * @param array $Roles The roles that this user has
     *
     * @return UserAccount
     */
    public function setRoles(array $Roles)
    {
        $this->roles = $Roles;
        return $this;
    }
    /**
     * Getter for the library
     * 
     * @return Library
     */ 
    public function getLibrary() 
    {
        return $this->library;
    }
    /**
     * Setter for the library
     * 
     * @param Library $value The library the user belongs to
     * 
     * @return UserAccount
     */
    public function setLibrary(Library $value) 
    {
        $this->library = $value;
        return $this;
    }
    /**
     * Cleanup all inactive product shelf items for the current user
     * 
     * @return UserAccount
     */
    public function deleteInactiveShelfItems()
    {
    	$sql = "select p.productId from productshelfitem p left join product pro on (pro.id = p.productId and pro.active = 1) where pro.id is null;";
    	$productIds = array_map(create_function('$a', 'return trim($a[0]);'), Dao::getResultsNative($sql, array(), PDO::FETCH_NUM));
    	if(count($productIds) > 0)
    		ProductShelfItem::deleteByCriteria('productId in (' . implode(', ', $productIds) . ')  AND ownerId = ' . $this->getId());
    	ProductShelfItem::deleteByCriteria('active = 0 AND ownerId = ' . $this->getId());
    	return $this;
    }
    /**
     * Getting the bookshelfitems for a user
     * 
     * @param int   $pageNo
     * @param int   $pageSize
     * @param array $orderby
     * 
     * @return array
     */
    public function getBookShelfItem($pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderby = array(), $activeOnly = true, &$stats = array())
    {
    	if(intval($this->getId()) === self::ID_GUEST_ACCOUNT)
    		return array();
    	$this->deleteInactiveShelfItems();
    	return ProductShelfItem::getAllByCriteria('ownerId = ?', array($this->getId()), $activeOnly, $pageNo, $pageSize, $orderby, $stats);
    }
    /**
     * Getting the count of bookshelfitem for this user
     * 
     * @return int
     */
    public function countBookShelfItem()
    {
    	if(intval($this->getId()) === self::ID_GUEST_ACCOUNT)
    		return 0;
    	$this->deleteInactiveShelfItems();
    	return ProductShelfItem::countByCriteria('ownerId = ? and active = ? ', array($this->getId(), 1));
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__toString()
     */
    public function __toString()
    {
        return $this->getUserName();
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntityAbstract::getJson()
     */
    public function getJson($extra = '', $reset = false)
    {
    	$array = array();
    	if(!$this->isJsonLoaded($reset))
    	{
    		$array['person'] = $this->getPerson()->getJson();
    		$array['roles'] = array();
    		foreach($this->getRoles() as $role)
    			$array['roles'][] = $role->getJson();
    	}
    	return parent::getJson($array, $reset);
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__loadDaoMap()
     */
    public function __loadDaoMap()
    {
        DaoMap::begin($this, 'ua');
        DaoMap::setStringType('username', 'varchar', 100);
        DaoMap::setStringType('password', 'varchar', 40);
        DaoMap::setManyToOne("person", "Person", "p");
        DaoMap::setManyToMany("roles", "Role", DaoMap::LEFT_SIDE, "r", false);
        DaoMap::setManyToOne('library', 'Library', 'lib');
        parent::__loadDaoMap();
        
        DaoMap::createIndex('username');
        DaoMap::createIndex('password');
        DaoMap::commit();
    }
    /**
     * Getting UserAccount
     *
     * @param string  $username The username string
     * @param string  $password The password string
     * @param Library $library  The library the user belongs to
     *
     * @throws AuthenticationException
     * @throws Exception
     * @return Ambigous <BaseEntityAbstract>|NULL
     */
    public static function getUserByUsernameAndPassword($username, $password, Library $library, $noHashPass = false)
    {
    	$query = self::getQuery();
    	$query->eagerLoad('UserAccount.roles', DaoQuery::DEFAULT_JOIN_TYPE, 'r');
    	$query->eagerLoad('UserAccount.library', DaoQuery::DEFAULT_JOIN_TYPE, 'lib');
    	$userAccounts = self::getAllByCriteria("`UserName` = :username AND `Password` = :password AND r.id != :roleId and lib.id = :libId", array('username' => $username, 'password' => ($noHashPass === true ? $password : sha1($password)), 'roleId' => Role::ID_GUEST, 'libId' => $library->getId()), false, 1, 2);
    	if(count($userAccounts) === 1)
    		return $userAccounts[0];
    	if(count($userAccounts) > 1)
    		throw new AuthenticationException("Multiple Users Found!Contact you administrator!");
    	return null;
    }
    /**
     * Getting all the libadmin users for a library
     * 
     * @param Library $library    The library the user belongs to
     * @param bool    $activeOnly
     * @param string  $pageNo
     * @param unknown $pageSize
     * @param unknown $orderBy
     * @param unknown $stats
     * 
     * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
     */
    public static function getLibAdminUsers(Library $library, $activeOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
    {
    	$query = self::getQuery();
    	$query->eagerLoad('UserAccount.roles', DaoQuery::DEFAULT_JOIN_TYPE, 'r');
    	$query->eagerLoad('UserAccount.library', DaoQuery::DEFAULT_JOIN_TYPE, 'lib');
    	return self::getAllByCriteria("r.id = :roleId and lib.id = :libId", array('roleId' => Role::ID_LIB_ADMIN, 'libId' => $library->getId()), $activeOnly, $pageNo, $pageSize, $orderBy, $stats);
    }
    /**
     * Getting UserAccount by username
     *
     * @param string $username    The username string
     *
     * @throws AuthenticationException
     * @throws Exception
     * @return Ambigous <BaseEntityAbstract>|NULL
     */
    public static function getUserByUsername($username, Library $library)
    {
    	$query = self::getQuery();
    	$query->eagerLoad('UserAccount.roles', DaoQuery::DEFAULT_JOIN_TYPE, 'r');
    	$query->eagerLoad('UserAccount.library', DaoQuery::DEFAULT_JOIN_TYPE, 'lib');
    	$userAccounts = self::getAllByCriteria("`UserName` = :username  AND r.id != :roleId and lib.id = :libId", array('username' => $username, 'roleId' => Role::ID_GUEST, 'libId' => $library->getId()), false, 1, 2);
    	if(count($userAccounts) === 1)
    		return $userAccounts[0];
    	else if(count($userAccounts) > 1)
    		throw new AuthenticationException("Multiple Users Found!Contact you administrator!");
    	else
    		return null;
    }
    /**
     * Creating a new useraccount
     *
     * @param string $username
     * @param string $password
     * @param Role   $role
     * @param Person $person
     *
     * @return UserAccount
     */
    public static function createUser(Library $lib, $username, $password, Role $role, Person $person)
    {
    	if(self::getUserByUsername($username, $lib) instanceof UserAccount)
    		throw new EntityException('System Error: trying to create a username with the same id:' . $username . '!');
    	$userAccount = new UserAccount();
    	$userAccount->setUserName($username)
    		->setPassword($password)
    		->setPerson($person)
    		->setLibrary($lib)
    		->save();
    	self::saveManyToManyJoin($role, $userAccount);
    	return self::get($userAccount->getId());
    }
    /**
     * Updating an useraccount
     *
     * @param UserAccount $userAccount
     * @param string      $username
     * @param string      $password
     * @param Role        $role
     * @param Person      $person
     *
     * @return Ambigous <BaseEntity, BaseEntityAbstract>
     */
    public static function updateUser(UserAccount &$userAccount, Library $lib, $username, $password, array $allRoles = null, Person $newPerson = null)
    {
    	$person = $userAccount->getPerson();
    	//user wants to update the person
    	if($newPerson instanceof Person)
    	{
    		$person = $newPerson->setId($person->getId())
    			->save();//update the old person with all the information from newPerson
    	}
    	$userAccount->setUserName($username)
    		->setPassword($password)
    		->setPerson($person)
    		->setLibrary($lib)
    		->save();
    	 
    	//if we are trying to update the roles too!
    	if(count($allRoles) > 0)
    	{
    		Dao::deleteByCriteria('role_useraccount', 'userAccountId = ?', array($userAccount->getId()));
    		//need to create whatever has left after the loop
    		foreach($allRoles as $role)
    		{
    			self::saveManyToManyJoin($role, $userAccount);
    		}
    	}
    	return ($userAccount = self::get($userAccount->getId())); //refersh the object
    }
}

?>
