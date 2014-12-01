<?php
/**
 * Test case for Dao - DaoQuery
 *
 * @package    Test
 * @subpackage Core
 * @since      2012-09-01
 * @author     lhe<helin16@gmail.com>
 */
class DaoQueryUnitTest extends CoreDaoUnitTestAbstract
{
    /**
     * DaoQuery instance
     * @var DaoQuery
     */
    private $_daoQuery = null;
    /**
     * (non-PHPdoc)
     * @see CoreUnitTestAbstract::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $this->_daoQuery = new DaoQuery($this->_focus, 1, 30);
    }
    /**
     * (non-PHPdoc)
     * @see CoreUnitTestAbstract::tearDown()
     */
    public function tearDown()
    {
        $this->_daoQuery = null;
        parent::tearDown();
    }
    /**
     * test DaoQuery::toString()
     */
    public function testToString()
    {
        $expected = 'DaoQuery("' . $this->_focus . '")';
        $actual = $this->_daoQuery->__toString();
        $this->assertEquals($expected, $actual, "We should have ($expected), but got($actual)!");
    }
    /**
     * test DaoQuery::getFocusClass()
     */
    public function testGetFocusClass()
    {
        $expected = $this->_focus;
        $actual = $this->_daoQuery->getFocusClass();
        $this->assertEquals($expected, $actual, "We should have ($expected), but got($actual)!");
    }
    /**
     * test DaoQuery::getPageStats()
     */
    public function testGetPageStats()
    {
        $this->_daoQuery = new DaoQuery($this->_focus, 1, 30);
        $expected = array(1, 30);
        $actual = $this->_daoQuery->getPageStats();
        $this->assertEquals($expected, $actual);
        $this->assertTrue($this->_daoQuery->isPaged());
        
        $this->_daoQuery = new DaoQuery($this->_focus, null, 30);
        $this->assertFalse($this->_daoQuery->isPaged());
    }
    /**
     * test DaoQuery::generateForSelect()
     */
    public function testGenerateForSelect()
    {
        $expected = 'select distinct sql_calc_found_rows p.`id`, p.`firstName`, p.`lastName`, p.`active`, p.`created`, p.`createdById`, p.`updated`, p.`updatedById` from person p where (p.active = 1) limit 0, 30';
        $actual = $this->_daoQuery->generateForSelect();
        $this->assertEquals($expected, $actual, "We should have '$expected'(" . strlen($expected) . "), but got '$actual'(" . strlen($actual) . ")!");
        $this->assertTrue($this->_daoQuery->isPaged());
        
        $expected = 'select distinct p.`id`, p.`firstName`, p.`lastName`, p.`active`, p.`created`, p.`createdById`, p.`updated`, p.`updatedById` from person p where (p.active = 1)';
        $this->_daoQuery->getPage(null);
        $actual = $this->_daoQuery->generateForSelect();
        $this->assertEquals($expected, $actual, "We should have '$expected'(" . strlen($expected) . "), but got '$actual'(" . strlen($actual) . ")!");
        $this->assertFalse($this->_daoQuery->isPaged());
        
        $expected = 'select distinct p.`id`, p.`firstName`, p.`lastName`, p.`active`, p.`created`, p.`createdById`, p.`updated`, p.`updatedById` from person p inner join useraccount `ua` on (p.id = ua.personId) where (p.active = 1)';
        $this->_daoQuery->eagerLoad("Person.userAccounts");
        $actual = $this->_daoQuery->generateForSelect();
        $this->assertEquals($expected, $actual, "We should have '$expected'(" . strlen($expected) . "), but got '$actual'(" . strlen($actual) . ")!");
        $this->assertFalse($this->_daoQuery->isPaged());
    }
    /**
     * test DaoQuery::generateForUpdate()
     */
    public function testGenerateForUpdate()
    {
        $expected = 'update person set `firstName`= :firstName, `lastName`= :lastName, `active`= :active, `created`= :created, `createdById`= :createdBy, `updated`= :updated, `updatedById`= :updatedBy where id = :id';
        $actual = $this->_daoQuery->generateForUpdate();
        $this->assertEquals($expected, $actual, "We should have '$expected'(" . strlen($expected) . "), but got '$actual'(" . strlen($actual) . ")!");
    }
    /**
     * test DaoQuery::generateForInsert()
     */
    public function testGenerateForInsert()
    {
        $expected = 'insert into person (`firstName`, `lastName`, `active`, `created`, `createdById`, `updated`, `updatedById`) values (:firstName, :lastName, :active, :created, :createdBy, :updated, :updatedBy)';
        $actual = $this->_daoQuery->generateForInsert();
        $this->assertEquals($expected, $actual, "We should have '$expected'(" . strlen($expected) . "), but got '$actual'(" . strlen($actual) . ")!");
    }
    /**
     * test DaoQuery::generateForDelete()
     */
    public function testGenerateForDelete()
    {
        $expected = 'delete from person where id = :id';
        $actual = $this->_daoQuery->generateForDelete();
        $this->assertEquals($expected, $actual, "We should have '$expected'(" . strlen($expected) . "), but got '$actual'(" . strlen($actual) . ")!");
    }
    /**
     * test DaoQuery::generateForCount()
     */
    public function testGenerateForCount()
    {
        $expected = 'select count(distinct p.id) `count` from person p where (p.active = 1)';
        $actual = $this->_daoQuery->generateForCount();
        $this->assertEquals($expected, $actual, "We should have '$expected'(" . strlen($expected) . "), but got '$actual'(" . strlen($actual) . ")!");
        
        $expected = 'select count(p.id) `count` from person p where (p.active = 1) AND (p.firstName like ?)';
        $this->_daoQuery->distinct(false);
        $this->_daoQuery->where("p.firstName like ?");
        $actual = $this->_daoQuery->generateForCount();
        $this->assertEquals($expected, $actual, "We should have '$expected'(" . strlen($expected) . "), but got '$actual'(" . strlen($actual) . ")!");
    }
}
?>