<?php
class LoginController extends FrontEndPageAbstract
{
    protected function _getEndJs()
    {
        $js = parent::_getEndJs();
        $js .= 'pageJs.setCallbackId("login", "' . $this->loginBtn->getUniqueID() . '");';
        return $js;
    }
    
    public function login($sender, $params)
    {
        $errors = $results = array();
        try 
        {
            if(!isset($params->CallbackParameter->username) || ($username = trim($params->CallbackParameter->username)) === '')
                throw new Exception('username not provided!');
            if(!isset($params->CallbackParameter->password) || ($password = trim($params->CallbackParameter->password)) === '')
                throw new Exception('password not provided!');
            
            if(isset($_REQUEST['return']) && ($returnUrl = trim($_REQUEST['return'])) !== '' )
            	WebUserManager::$fromLocalDB = true;
            
            $authManager=$this->getApplication()->getModule('auth');
            if(!$authManager->login($username, $password))
            	throw new Exception('Invalid username or password!');
            
            if(isset($_REQUEST['return']) && ($returnUrl = trim($_REQUEST['return'])) !== '' )
            	WebUserManager::$fromLocalDB = false;
            
            if(Core::getRole() instanceof Role && in_array(trim(Core::getRole()->getId()), array(trim(role::ID_ADMIN), trim(Role::ID_LIB_ADMIN))) 
            	&& isset($_REQUEST['return']) && ($returnUrl = trim($_REQUEST['return'])) !== '' 
           	)
            {
            	$results['url'] = $returnUrl;
            }
            else
            	$results['url'] = '/user.html';
        }
        catch(Exception $ex)
        {
        	$errors[] = $ex->getMessage();
        }
        $params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
    }
}