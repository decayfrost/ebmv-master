<?php
error_reporting(E_ALL & ~E_NOTICE);
/**
 * 
 * Test Module
 * 
 * @package    Test
 * @subpackage Class
 * @since      2012-09-01
 * @author     lhe<lhe@bytecraft.com.au>
 *
 */
abstract class TestLoader
{
    /**
     * autoloading function
     * 
     * @param string $className The class that we are trying to autoloading
     * 
     * @return boolean Whether we loaded the class
     */
	public static function autoload($className)
	{
		$autoloadPaths = array(
			'/class/',
		);
	    $baseDir = dirname(__FILE__);
		foreach ($autoloadPaths as $path)
		{
		    $filePath = $baseDir . $path . $className . '.php';
			if (file_exists($filePath) === true)
			{
				require_once $filePath;
				return true;
			}
		}
		return false;
	}
}

spl_autoload_register(array('TestLoader','autoload'));

// PHPUnit
require_once 'PHPUnit/Autoload.php';

// core
require dirname(__FILE__) . '/../main/bootstrap.php';
?>