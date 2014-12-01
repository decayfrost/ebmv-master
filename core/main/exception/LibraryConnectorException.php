<?php
/**
 * The LibraryConnector Exception
 * 
 * @package    Core
 * @subpackage Exception
 * @author     lhe<helin16@gmail.com>
 */
class LibraryConnectorException extends Exception
{
	public function __construct($message, $code = 200, $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}

?>