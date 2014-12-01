<?php
/**
 *
 * Abstract Core Unit Test for Entity
 *
 * @package    Test
 * @subpackage class
 * @author     lhe<helin16@gmail.com>
 * @since      2012-09-01
 *
 */
abstract class CoreEntityUnitTestAbstract extends CoreUnitTestAbstract
{
    /**
     * The testing entity's class name
     * 
     * @var string
     */
    protected $_entityName = '';
    /**
     * BaseEntity object to test
     *
     * @var BaseEntity
     */
    protected $_entityObj = null;
    /**
     * The ID BaseEntity object
     *
     * @var int
     */
    protected $_entityId = null;
    /**
     * constructor
     * 
     * @param string $name     The name of the test case
     * @param array  $data     The data for testing
     * @param Mixed  $dataName The data name
     */
    public function __construct($name = NULL, array $data = array(), $dataName = '') 
    { 
        parent::__construct($name, $data, $dataName); 
        if($this->_entityId === null)
        {
            $this->_entityId = $this->_getEntityId();
        }
            
    }
    /**
     * The ID BaseEntity object
     * 
     * @return string
     */
    private function _getEntityId()
    {
        $sql = 'select id from ' . strtolower($this->_entityName) . ' order by id desc limit 1';
        $result = Dao::getResultsNative($sql);
        return (isset($result[0]['id']) ? trim($result[0]['id']) : null);
    }
    /**
     * (non-PHPdoc)
     * @see CoreUnitTestAbstract::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        if($this->_entityId === null)
        {
            $this->_entityObj = new $this->_entityName;
            $this->_entityObj->setId(1);
        }
        else
        {
            $this->_entityObj = Dao::findById(new DaoQuery($this->_entityName), $this->_entityId);
        }
    }
    /**
     * (non-PHPdoc)
     * @see CoreUnitTestAbstract::tearDown()
     */
    public function tearDown()
    {
        $this->_entityObj = null;
        parent::tearDown();
    }
    /**
     * testing BaseEntity's getters and setters
     *
     * @param BaseEntity $entity   The entity that we are testing against
     * @param string      $field    The field that we trying to set or get
     * @param Mixed       $expected The expected value for the field to set and get
     * 
     * @dataProvider getGSData
     */
    public function testGettersAndSetters($field, $expected)
    {
        $entity = $this->_entityObj;
        
        //testing whether we've got a setter
        $setMethod = 'set' . ucfirst($field);
        $className = get_class($entity);
        if (method_exists($entity, $setMethod) !== true)
        {
            throw new Exception('Method (' . $setMethod . ') does NOT exsits on ' . $className);
        }
        $entity->$setMethod($expected);
    
        //testing whether we've got a getter
        $getMethod = 'get' . ucfirst($field);
        if (method_exists($entity, $getMethod) !== true)
        {
            throw new Exception('Method (' . $getMethod . ') does NOT exsits on ' . $className);
        }
        
        //testing against the value
        $actual = $entity->$getMethod();
        if (is_array($actual) && is_array($expected) && count($actual) > 0 && count($expected) > 0 && $actual[0] instanceof BaseEntity && $expected[0] instanceof BaseEntity)
        {
            usort($actual, array(get_class($this), 'sortHYEntities'));
            usort($expected, array(get_class($this), 'sortHYEntities'));
            for($i = 0, $size = count($actual); $i< $size; $i++)
            {
                $this->assertingBaseEntity($actual[$i], $expected[$i], $i);
            }
        }
        else
        {
            $this->assertEquals($expected, $actual, 'Expected value NOT matched for field:"' . $field . '": should get (' . $expected. ', count=' . count($expected) . ') but got (' . $actual . ', count=' . count($actual) . '). ');
        }
    }
    /**
     * asserting the hydra entity
     * 
     * @param BaseEntity $entity1 The hydra entity
     * @param BaseEntity $entity2 The hydra entity
     * @param int         $index   The index of the array
     * 
     * @return CoreEntityUnitTestAbstract
     */
    public function assertingBaseEntity(BaseEntity $entity1, BaseEntity $entity2, $index)
    {
        $this->assertEquals($entity1, $entity2, $index . ' - Entity (ID = ' . $entity1->getId() . ') is NOT the same as Entity (ID = ' . $entity2->getId() . ')!');
        return $this;
    }
    /**
     * sorting Hydra Entity array
     * 
     * @param BaseEntity $entity1 The hydra entity in the array
     * @param BaseEntity $entity2 The hydra entity in the array
     * 
     * @return int
     */
    public static function sortHYEntities(BaseEntity $entity1, BaseEntity $entity2)
    {
        if($entity1->getId() === $entity2->getId())
        {
            return 0 ;
        }
        return ($entity1->getId() < $entity2->getId()) ? -1 : 1;
    }
    /**
     * get the testing DATA for testGettersAndSetters()
     * 
     * @return Mixed The data for getters and setters
     */
    public function getGSData()
    {
        $testingDataForGS = array();
        DaoMap::loadMap($this->_entityName);
        $map = DaoMap::$map[strtolower($this->_entityName)];
        foreach ($map as $field => $info)
        {
            if(trim($field) === '_')
            {
                continue;
            }
            $newValue = null; //the value for the setter of the field
            if (isset($info['class']) && isset($info['rel'])) //if we are loading another entity
            {
                $loadingClassName = trim($info['class']);
                $q = new DaoQuery($loadingClassName);
                $q->distinct(true);
                
                //if we are requiring an array of objects for the setters
                if (in_array($info['rel'], array(DaoMap::MANY_TO_MANY, DaoMap::ONE_TO_MANY)))
                {
                    DaoMap::loadMap($loadingClassName);
                    $alias = DaoMap::$map[strtolower($loadingClassName)]['_']['alias'];
                    $clsfield = strtolower(substr($this->_entityName, 0, 1)) . substr($this->_entityName, 1);
                    if ($info['rel'] === DaoMap::MANY_TO_MANY)
                    {
                        if ($info['side'] === DaoMap::LEFT_SIDE)
                            $crossTableName = strtolower($loadingClassName . '_' . $this->_entityName);
                        else if ($info['side'] === DaoMap::RIGHT_SIDE)
                            $crossTableName = strtolower($this->_entityName . '_' . $loadingClassName);
                        else
                            throw new Exception("Invalide relationship has been setup between '$loadingClassName' and '{$this->_entityName}'.");
                        
                        $sql = "select distinct " . (strtolower(substr($loadingClassName, 0, 1)) . substr($loadingClassName, 1)) . "Id `id` from $crossTableName where " . $clsfield . "Id = " . $this->_entityId;
                        $result = Dao::getResultsNative($sql);
                        if (count($result) === 0)
                            $newValue = array();
                        else 
                            $newValue = Dao::findByCriteria($q, $alias . ".id in (" . implode(",", array_map(create_function('$a', 'return $a["id"];'), $result)). ")" , array($this->_entityId));
                    }
                    else
                        $newValue = Dao::findByCriteria($q, sprintf('%s.`%sId`=?', $alias, $clsfield), array($this->_entityId));
                }
                else //if we are requiring one object for the setters
                {
                    //hack here to get the last id from the entity table
                    $result = Dao::getResultsNative('select id from ' . strtolower($loadingClassName) . ' order by id desc limit 1');
                    $id = isset($result[0][0]) ? trim($result[0][0]) : 1;
                    $newValue = Dao::findById($q, $id);
                }
            }
            else //if we are dealling with just the value not entities
            {
                switch(strtolower($info['type']))
                {
                    case 'varchar':
                    {
                        $newValue = 'testingVarchar';
                        break;
                    }
                    case 'int':
                    {
                        $newValue = 1;
                        break;
                    }
                    case 'bool':
                    {
                        $newValue = true;
                        break;
                    }
                    case 'datetime':
                    {
                        $newValue = new UDate();
                        break;
                    }
                    case 'double':
                    {
                        $newValue = '0.000000';
                        break;
                    }
                    default:
                    {
                        $newValue = null;
                    }
                }
            }
            
            //assigning the field with expecting value
            $testingDataForGS[] = array($field, $newValue);
        }
        return $testingDataForGS;
    }
    /**
     * common testing function for UDate for getters and setters
     * 
     * @param string $setFunction The set function for the field
     * @param string $getFunction The get function for the field
     * @param string $dateString  The date string like '2010-09-10 00:00:00'
     * 
     * @return CoreEntityUnitTestAbstract
     */
    protected function _testGetDateString($setFunction, $getFunction, $dateString = '2010-09-10 00:00:00')
    {
        $this->_entityObj->$setFunction($dateString);
        $this->assertEquals(new UDate($dateString), $this->_entityObj->$getFunction(), $this->_entityName . "::$getFunction() should return a UDate object!");
        
        $this->_entityObj->$setFunction(new UDate($dateString));
        $this->assertEquals(new UDate($dateString), $this->_entityObj->$getFunction(), $this->_entityName . "::$getFunction() should return a UDate object!");
        
        return $this;
    }
    /**
     * Testing the normal BaseEntity's __toString()
     * 
     * @param $expected The expected value for __toString()
     */
    protected function _testToString($expected = null)
    {
        $expected = ($expected === null ? $this->_entityName . ' (#' . $this->_entityObj->getId() . ')' : $expected);
        $toString = $this->_entityObj->__toString();
        $this->assertEquals($expected, $this->_entityObj->__toString(), $this->_entityName . "::__toString() should get:'" . $expected . "'(" . strlen($expected) . ") and got: '" . $toString. "'(" . strlen($toString) . ").");
    }
}