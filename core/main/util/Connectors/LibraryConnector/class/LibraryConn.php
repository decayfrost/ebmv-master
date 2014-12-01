<?php
/**
 * Library connector interface
 *
 * @package    Core
 * @subpackage Utils
 * @author     lhe<helin16@gmail.com>
 */
interface LibraryConn
{
	/**
	 * Getting the library from the library connector
	 * 
	 * @return Library
	 */
	public function getLibrary();
	/**
	 * Getting the user information for a user
	 * 
	 * @param unknown $username
	 * @param unknown $password
	 * 
	 * @return LibraryConnectorUser
	 */
	public function getUserInfo($username, $password);
	/**
	 * Checking whether the user exists
	 * 
	 * @param unknown $username
	 * @param unknown $password
	 * 
	 * @return bool
	 */
	public function chkUser($username, $password);
}