<?php
/**
 * The mid data container for the library connector
 *
 * @package    Core
 * @subpackage Utils
 * @author     lhe<helin16@gmail.com>
 */
class LibraryConnectorUser
{
	/**
	 * The username
	 * 
	 * @var string
	 */
	private $_username;
	/**
	 * The password for this user
	 * 
	 * @var string
	 */
	private $_password;
	/**
	 * The library
	 * @var Library
	 */
	private $_library;
	private $_firstName;
	private $_lastName;
	private $_infos;
	/**
	 * The cache for the static getUser function
	 * 
	 * @var array
	 */
	private static $_cache;
	/**
	 * Getting the LibraryConnectorUser
	 * 
	 * @param unknown $username
	 * @param unknown $password
	 * 
	 * @return LibraryConnectorUser
	 */
	public static function getUser(Library $lib, $username, $password, $firstName, $lastName, $infos = array()){
		$key = md5($lib->getId() . $username . $password);
		if(!isset($_cache[$key]))
		{
			$class = trim(get_called_class());
			$user = new $class;
			$user->setLibrary($lib);
			$user->setUsername($username);
			$user->setPassword($password);
			$user->setFirstName(trim($firstName));
			$user->setLastName(trim($lastName));
			$user->setInfos($infos);
			self::$_cache[$key] = $user;
		}
		return self::$_cache[$key];
	}
	/**
	 * Getter for username
	 *
	 * @return string
	 */
	public function getUsername() 
	{
	    return $this->_username;
	}
	/**
	 * Setter for username
	 *
	 * @param string $value The username
	 *
	 * @return string
	 */
	public function setUsername($value) 
	{
	    $this->_username = $value;
	    return $this;
	}
	/**
	 * Getter for _password
	 *
	 * @return string
	 */
	public function getPassword() 
	{
	    return $this->_password;
	}
	/**
	 * Setter for _password
	 *
	 * @param unkown $value The _password
	 *
	 * @return LibraryConnectorUser
	 */
	public function setPassword($value) 
	{
	    $this->_password = $value;
	    return $this;
	}
	/**
	 * Getter for _firstName
	 *
	 * @return string
	 */
	public function getFirstName() 
	{
	    return $this->_firstName;
	}
	/**
	 * Setter for _firstName
	 *
	 * @param unkown $value The _firstName
	 *
	 * @return LibraryConnectorUser
	 */
	public function setFirstName($value) 
	{
	    $this->_firstName = $value;
	    return $this;
	}
	/**
	 * Getter for _lastname
	 *
	 * @return string
	 */
	public function getLastName() 
	{
	    return $this->_lastName;
	}
	/**
	 * Setter for _lastname
	 *
	 * @param unkown $value The _lastname
	 *
	 * @return LibraryConnectorUser
	 */
	public function setLastName($value) 
	{
	    $this->_lastName = $value;
	    return $this;
	}
	/**
	 * Getter for _library
	 *
	 * @return Library
	 */
	public function getLibrary() 
	{
	    return $this->_library;
	}
	/**
	 * Setter for _library
	 *
	 * @param unkown $value The _library
	 *
	 * @return LibraryConnectorUser
	 */
	public function setLibrary(Library $value) 
	{
	    $this->_library = $value;
	    return $this;
	}
	/**
	 * Getter for _infos
	 *
	 * @return infos
	 */
	public function getInfos() 
	{
	    return $this->_infos;
	}
	/**
	 * Setter for _infos
	 *
	 * @param unkown $value The _infos
	 *
	 * @return infosConnectorUser
	 */
	public function setInfos(array $value) 
	{
	    $this->_infos = $value;
	    return $this;
	}
}