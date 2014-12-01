<?php
class LogoutController extends TPage
{
	public function onLoad($param)
	{
		$redirectUrl = (isset($_REQUEST['url']) && trim($_REQUEST['url']) !== '') ? trim($_REQUEST['url']) : '/';
		$auth = $this->getApplication()->Modules['auth'];
		$auth->logout();
		$this->Response->Redirect($redirectUrl);
	}
}