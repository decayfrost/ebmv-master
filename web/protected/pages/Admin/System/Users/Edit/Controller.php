<?php
/**
 * This is the listing page for library user account
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class Controller extends AdminPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see FrontEndPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		if(!($lib = Library::get($this->request['libraryId'])) instanceof Library)
			die('invalid library');
		if(is_numeric(($id = trim($this->request['userId']))))
		{
			$user = UserAccount::get($id);
			if(!$user instanceof UserAccount)
				die('invalid userAccount');
		}
		else if($id === 'new')
			$user = new UserAccount();
		
		$js = parent::_getEndJs();
		$js .= 'pageJs.setUser(' . json_encode(trim($user->getId()) === '' ? array() : $user->getJson()) . ')';
		$js .= '.setLibrary(' . json_encode($lib->getJson()) . ')';
		$js .= '.setCallbackId("saveUser", "' . $this->saveUserBtn->getUniqueID() . '")';
		$js .= '.load("details-div")';
		$js .= ';';
		return $js;
	}
	/**
	 * (non-PHPdoc)
	 * @see LibAdminPageAbstract::onInit()
	 */
	public function onInit($params)
	{
		parent::onInit($params);
		$this->getPage()->setTheme($this->_getThemeByName('default'));
	}
	/**
	 * saving a user
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 */
	public function saveUser($sender, $param)
	{
		$result = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->libraryId) || !($lib = Library::get($param->CallbackParameter->libraryId)) instanceof Library)
				throw new Exception('Invalid library!');
			if(!isset($param->CallbackParameter->firstName) || ($firstName = trim($param->CallbackParameter->firstName)) === '')
				throw new Exception('Invalid firstName!');
			if(!isset($param->CallbackParameter->lastName) || ($lastName = trim($param->CallbackParameter->lastName)) === '')
				throw new Exception('Invalid lastName!');
			if(!isset($param->CallbackParameter->username) || ($username = trim($param->CallbackParameter->username)) === '')
				throw new Exception('Invalid username!');
			if(!isset($param->CallbackParameter->userId))
				throw new Exception('Invalid user id!');
			if(!isset($param->CallbackParameter->password))
				throw new Exception('Invalid password!');
			$password = trim($param->CallbackParameter->password);
			if(($userId = trim($param->CallbackParameter->userId)) === '')
			{
				if($password === '')
					throw new Exception('Blank password is NOT acceptable!');
				$user = UserAccount::createUser($lib, $username, sha1($password), Role::get(Role::ID_LIB_ADMIN), Person::createNudpatePerson($firstName, $lastName));
			}
			else if(($user = UserAccount::get($param->CallbackParameter->userId)) instanceof UserAccount)
			{
				if($password === '')
					$password = $user->getPassword();
				else
					$password = sha1($password);
				$user->getPerson()
					->setFirstName($firstName)
					->setLastName($lastName)
					->save();
				$user = UserAccount::updateUser($user, $lib, $username, $password);
			}
			else 
				throw new Exception('Invalid user!');
			
			$result['item'] = $user->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
			
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
}