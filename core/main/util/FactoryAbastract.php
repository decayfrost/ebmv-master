<?php
/**
 * The abstract class
 * 
 * @author lhe
 *
 */
abstract class FactoryAbastract
{
	// Define registery holders
	private static $_elements = array();
    /**
     * Getting the singleton dao object
     * 
	 * @param string $entityClassName The entity class name of the dao
	 * @throws HydraDaoException
     * 
     * @return EntityDao
     */
	public static function dao($entityClassName)
	{
		if(!isset(self::$_elements[$entityClassName]))
		{
			if(!class_exists($entityClassName))
				throw new DaoException("Invalid class : ".$entityClassName);
			self::$_elements[$entityClassName] = new EntityDao($entityClassName);
		}
		return self::$_elements[$entityClassName];
	}
}