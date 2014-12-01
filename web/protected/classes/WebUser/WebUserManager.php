<?php
Prado::using('System.Security.IUserManager');
Prado::using('Application.classes.WebUser.WebUser');
/**
 * Manager for Web Users extends TModule implements IUserManager
 *
 */
class WebUserManager extends TModule implements IUserManager
{
	public static $fromLocalDB = false;
	/**
	 * get the Guest Name
	 *
	 * @return unknown
	 */
	public function getGuestName()
	{
		return 'Guest';
	}

	/**
	 * Get the WebUser
	 *
	 * @param unknown_type $username
	 * @return WebUser
	 */
	public function getUser($username=null)
	{
		if($username === null)
			return new WebUser($this);
		
		if(!($userAccount = (Core::getUser() instanceof UserAccount ? Core::getUser(): UserAccount::getUserByUsername($username, Core::getLibrary()))) instanceof UserAccount)
			return null;
		
		$user = new WebUser($this);
		$user->setUserAccount($userAccount);
		$user->setName($userAccount->getUsername());
		$user->setIsGuest(false);
		$user->setRoles($userAccount->getRoles());
		return $user;
	}
	
	/**
	 * validate a user providing $username and $password
	 *
	 * @param string $username
	 * @param string $password
	 * @return true, if there is such a userAccount in the database;otherwise, false;
	 */
	public function validateUser($username, $password)
	{
		if(!Core::getUser() instanceof UserAccount)
		{
			if(!($userAccount = self::login(Core::getLibrary(), $username, $password)) instanceof UserAccount)
				return false;
		}
		return true;
	}
	
	/**
	 * Save a TUser to cookie
	 *
	 * @param unknown_type $cookie
	 */
	public function saveUserToCookie($cookie)
	{
		// TODO: do nothing at this moment,
		//since we don't support cookie-based auth
	}

	/**
	 * Get a TUser from Cookie
	 *
	 * @param unknown_type $cookie
	 * @return unknown
	 */
	public function getUserFromCookie($cookie)
	{
		// TODO: do nothing at this moment,
		//since we don't support cookie-based auth
		return null;
	}
	/**
	 * login for the library reader
	 *
	 * @param Library $lib
	 * @param unknown $username
	 * @param unknown $password
	 */
	public static function login(Library $lib, $libCardNo, $password)
	{
	
		if (! Core::getUser () instanceof UserAccount)
			Core::setUser ( UserAccount::get ( UserAccount::ID_SYSTEM_ACCOUNT ) );
		if(self::$fromLocalDB === true)
		{
			$userAccount = UserAccount::getUserByUsernameAndPassword($libCardNo, $password, $lib);
			// check whether the library has the user or not
			if (! $userAccount instanceof UserAccount)
				throw new CoreException ( 'Invalid login please contact ebmv admin!' );
		}
		else
		{
			// check whether the library has the user or not
			if (! LibraryConnectorAbstract::getScript ($lib)->chkUser ( $libCardNo, $password ))
				throw new CoreException ( 'Invalid login please contact your library!' );
				
			// get the information from the library system
			$userInfo = LibraryConnectorAbstract::getScript ($lib)->getUserInfo ( $libCardNo, $password );
			// check whether our local db has the record already
			if (($userAccount = UserAccount::getUserByUsername ( $libCardNo, $lib )) instanceof UserAccount) {
				$person = $userAccount->getPerson();
				$userAccount = UserAccount::updateUser ( $userAccount, $lib, $userInfo->getUsername (), $userInfo->getPassword (), null, Person::createNudpatePerson( $userInfo->getFirstName (), $userInfo->getLastName(), $person ) );
			} else 		// we need to create a user account from blank
			{
				$userAccount = UserAccount::createUser ( $lib, $userInfo->getUsername (), $userInfo->getPassword (), Role::get(Role::ID_READER), Person::createNudpatePerson( $userInfo->getFirstName (), $userInfo->getLastName() ) );
			}
		}
	
		$role = null;
		if (! Core::getRole () instanceof Role)
		{
			if (count ( $roles = $userAccount->getRoles () ) > 0)
				$role = $roles [0];
		}
		Core::setUser($userAccount, $role);
		return $userAccount;
	}
}
?>