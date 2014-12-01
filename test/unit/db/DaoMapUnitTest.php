<?php
/**
 * Test case for Dao - DaoMap
 *
 * @package    Test
 * @subpackage Core
 * @since      2012-09-01
 * @author     lhe<helin16@gmail.com>
 */
class DaoMapUnitTest extends CoreUnitTestAbstract
{
    private $_className = 'User';
    /**
     * pre-test for each test function
     */
    public function setUp()
    {
    }
    /**
     * post test for each test function
     */
    public function tearDown()
    {
        DaoMap::clearMap();
    }
    /**
     * testing DaoMap::loadMap() && DaoMap::getMap()
     */
	public function testLoadMap()
	{
		DaoMap::loadMap($this->_className);
		$map = DaoMap::getMap();
		$this->assertTrue(array_key_exists(strtolower($this->_className), $map));
	}
    /**
     * testing DaoMap::hasMap()  && DaoMap::clearMap()
     */
	public function testHasMap()
	{
	    DaoMap::loadMap($this->_className);
		$this->assertTrue(DaoMap::hasMap($this->_className));
		DaoMap::clearMap();
		$this->assertFalse(DaoMap::hasMap($this->_className));
	}
    /**
     * testing DaoMap::loadMap()
     * 
     * 
     * @expectedException        DaoException
     * @expectedExceptionMessage  does NOT exsits!
     */
	public function testHasMapWithException()
	{
		DaoMap::loadMap(null);
	}
    /**
     * testing DaoMap::hasMap()
     */
	public function testHasMapWithObject()
	{
		$this->assertFalse(DaoMap::hasMap('User'));
		$this->assertFalse(DaoMap::hasMap(new stdClass()));
		
		DaoMap::loadMap('User');
		$this->assertTrue(DaoMap::hasMap('User'));
		$this->assertTrue(DaoMap::hasMap(new User()));
		$this->assertFalse(DaoMap::hasMap('false'));
	}
    /**
     * testing DaoMap::begin()
     */
	public function testBegin()
	{
	    $class = 'User';
	    $object = new $class();
		DaoMap::begin($object);
		DaoMap::commit();
		
		//check on map key
		$map = DaoMap::getMap();
		$this->assertTrue(isset($map[strtolower($class)]));
		//check on alias key '_'
		$this->assertEquals(strtolower($class), $map[strtolower($class)]['_']['alias']);
		$this->assertEquals(null, $map[strtolower($class)]['_']['sort']);
	}
	
	public function testManyToOne()
	{
	    $alias = null;
	    $nullable = true;
	    $class = 'User';
	    $field = 'manytomany';
	    $defaultId = 0;
	    $object = new $class();
	    DaoMap::begin($object);
	    DaoMap::setManyToOne($field, $class, $alias, $nullable, $defaultId);
	    DaoMap::commit();
	    
	    //check on fields
	    $this->assertTrue(isset(DaoMap::$map[strtolower($class)][$field]));
	    $excepted = array('type' => 'int', 
    	    	'size' => 10,
    	    	'unsigned' => true,    	    	
    	    	'nullable' => $nullable,
    	    	'default' => $defaultId, 
    	    	'class' => $class, 
    	    	'alias' => $field,
    	    	'rel' => DaoMap::MANY_TO_ONE);
	    $this->assertEquals($excepted, DaoMap::$map[strtolower($class)][$field], "Should get: " . print_r($excepted, true) . " but got: " . print_r(DaoMap::$map[strtolower($class)][$field], true));
	}
	
	public function testManyToMany()
	{
	    $alias = 'test';
	    $nullable = true;
	    $defaultId = 
	    $class = 'User';
	    $field = 'manytomany';
	    $object = new $class();
	    DaoMap::begin($object);
	    DaoMap::setManyToMany($field, $class, DaoMap::LEFT_SIDE, $alias, $nullable);
	    DaoMap::commit();
	    
	    //check on fields
	    $this->assertTrue(isset(DaoMap::$map[strtolower($class)][$field]));
	    $excepted = array('type' => 'int',
	        	    	'size' => 10,
	        	    	'unsigned' => true,    	    	
	        	    	'nullable' => $nullable,
	        	    	'default' => intval($defaultId), 
	        	    	'class' => $class, 
	        	    	'alias' => $alias,
	    				'side' => DaoMap::LEFT_SIDE,
	        	    	'rel' => DaoMap::MANY_TO_MANY);
	    $this->assertEquals($excepted, DaoMap::$map[strtolower($class)][$field], "Should get: " . print_r($excepted, true) . " but got: " . print_r(DaoMap::$map[strtolower($class)][$field], true));
	}
	public function testOneToMany()
	{
	    $alias = 'test';
	    $nullable = true;
	    $defaultId = 
	    $class = 'User';
	    $field = 'manytomany';
	    $object = new $class();
	    DaoMap::begin($object);
	    DaoMap::setOneToMany($field, $class, $alias);
	    DaoMap::commit();
	    
	    //check on fields
	    $this->assertTrue(isset(DaoMap::$map[strtolower($class)][$field]));
	    $excepted = array('type' => 'int',
			'size' => 10,
			'unsigned' => true,
			'nullable' => false,
			'default' => 0,
			'class' => $class,
			'alias' => $alias,
			'rel' => DaoMap::ONE_TO_MANY
	    );
	    $this->assertEquals($excepted, DaoMap::$map[strtolower($class)][$field], "Should get: " . print_r($excepted, true) . " but got: " . print_r(DaoMap::$map[strtolower($class)][$field], true));
	}
	
	public function testOneToOne()
	{
	    $alias = null;
	    $nullable = true;
	    $class = 'User';
	    $field = 'onetoone';
	    $defaultId = 0;
	    $isOwner = true;
	    $object = new $class();
	    DaoMap::begin($object);
	    DaoMap::setOneToOne($field, $class, $isOwner, $alias, $nullable);
	    DaoMap::commit();
	    
	    //check on fields
	    $this->assertTrue(isset(DaoMap::$map[strtolower($class)][$field]));
	    $excepted = array('type' => 'int', 
    	    	'size' => 10,
    	    	'unsigned' => true,    	    	
    	    	'nullable' => $nullable,
    	    	'default' => $defaultId, 
    	    	'class' => $class, 
    	    	'alias' => $field,
    	    	'owner' => intval($isOwner),
    	    	'rel' => DaoMap::ONE_TO_ONE);
	    $this->assertEquals($excepted, DaoMap::$map[strtolower($class)][$field], "Should get: " . print_r($excepted, true) . " but got: " . print_r(DaoMap::$map[strtolower($class)][$field], true));
	}
}

?>