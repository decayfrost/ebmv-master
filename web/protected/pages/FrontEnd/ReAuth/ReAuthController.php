<?php
/**
 * This is the ReAuth Controller - for the Supplier to re-authenticate the host
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 *
 */
class ReAuthController extends TService
{
    /**
     * (non-PHPdoc)
     * @see TService::run()
     */
    public function run()
    {
    	if(!isset($_REQUEST['SiteID']))
    		die();
    	$CDKey = $this->_getRequestVar('CDKey');
    	$SiteID = $this->_getRequestVar('SiteID');
    	$Uid = $this->_getRequestVar('Uid');
    	$Pwd = $this->_getRequestVar('Pwd');
    	$webAuth = new WebAuth();
    	$response = $webAuth->authenticate($CDKey, $SiteID, $Uid, $Pwd);
        header('Content-type: text/xml; charset=utf-8');
        die($response);
    }
    /**
     * 
     * @param unknown $key
     * @param string $defaultValue
     * @return string
     */
    private function _getRequestVar($key, $defaultValue = '')
    {
    	return isset($_REQUEST[$key]) ? trim($_REQUEST[$key]) : $defaultValue;
    }
}