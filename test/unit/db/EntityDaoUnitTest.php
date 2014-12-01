<?php
/**
 * Test case for Dao - EntityDao
 *
 * @package    Test
 * @subpackage Core
 * @since      2012-09-01
 * @author     lhe<helin16@gmail.com>
 *
 */
class EntityDaoUnitTest extends CoreDaoUnitTestAbstract
{
    /**
     * The entity dao
     * @var EntityDAO
     */
    private $_dao;
    /**
     * (non-PHPdoc)
     * @see CoreUnitTestAbstract::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        Dao::beginTransaction();
        $this->_dao = new EntityDao($this->_focus);
    }
    /**
     * (non-PHPdoc)
     * @see CoreUnitTestAbstract::tearDown()
     */
    public function tearDown()
    {
        $this->_dao = null;
        Dao::rollbackTransaction();
        parent::tearDown();
    }
    /**
     * testing the EntityDao::getQuery() function
     */
    public function testGetQuery()
    {
        $query = $this->_dao->getQuery();
        $this->assertTrue($query instanceof DaoQuery);
        $this->assertEquals($this->_focus, $query->getFocusClass());
    }
    /**
     * testing the EntityDao::findById(), EntityDao::save(), EntityDao::getAffectedRows() and EntityDao::getLastId() function
     */
    public function testFindnSave()
    {
        $entity = $this->_dao->findById(1);
        $this->assertTrue($entity instanceof $this->_focus);
        $this->assertEquals('1', $entity->getId());
        
        $dao = $this->_dao->save($entity);
        $this->assertEquals($this->_dao, $dao);
        $this->assertEquals(1, $this->_dao->getAffectedRows());
        $this->assertEquals(-1, $this->_dao->getLastId());
        
        $affectedRows = $this->_dao->delete($entity);
        $this->assertEquals($this->_dao->getAffectedRows(), $affectedRows);
        $this->assertEquals(1, $this->_dao->getAffectedRows());
        $this->assertEquals(-1, $this->_dao->getLastId());
        
        $affectedRows = $this->_dao->deactivate($entity);
        $this->assertEquals($this->_dao->getAffectedRows(), $affectedRows);
        $this->assertEquals(1, $this->_dao->getAffectedRows());
        $this->assertEquals(-1, $this->_dao->getLastId());
        
        $affectedRows = $this->_dao->activate($entity);
        $this->assertEquals($this->_dao->getAffectedRows(), $affectedRows);
        $this->assertEquals(1, $this->_dao->getAffectedRows());
        $this->assertEquals(-1, $this->_dao->getLastId());
        
        $entity->setId(null);
        $dao = $this->_dao->save($entity);
        $this->assertEquals($this->_dao, $dao);
        $this->assertEquals(1, $this->_dao->getAffectedRows());
        $this->assertEquals($entity->getId(), $this->_dao->getLastId());
    }
    /**
     * testing the EntityDao::findByCriteria()
     */
    public function testFindByCriteria()
    {
        $entities = $this->_dao->findByCriteria('active = ?', array(1), 1, DaoQuery::DEFAUTL_PAGE_SIZE, array("id" => 'asc'));
        $this->assertGreaterThan(0, count($entities));
        $this->assertLessThan(DaoQuery::DEFAUTL_PAGE_SIZE + 1, count($entities));
        $pageStats = $this->_dao->getPageStats();
        $this->assertGreaterThanOrEqual(count($entities), $pageStats['totalRows']);
        $this->assertGreaterThanOrEqual(1, $pageStats['pageNumber']);
        $this->assertEquals(DaoQuery::DEFAUTL_PAGE_SIZE, $pageStats['pageSize']);
        $this->assertEquals(ceil($pageStats['totalRows'] / DaoQuery::DEFAUTL_PAGE_SIZE), $pageStats['totalPages']);
        $this->assertInstanceOf($this->_focus, $entities[0]);
    }
    /**
     * testing the EntityDao::findAll()
     */
    public function testFindAll()
    {
        $entities = $this->_dao->findAll(1, DaoQuery::DEFAUTL_PAGE_SIZE);
        $this->assertGreaterThan(0, count($entities));
        $this->assertLessThan(DaoQuery::DEFAUTL_PAGE_SIZE + 1, count($entities));
        
        $pageStats = $this->_dao->getPageStats();
        $this->assertGreaterThanOrEqual(count($entities), $pageStats['totalRows']);
        $this->assertGreaterThanOrEqual(1, $pageStats['pageNumber']);
        $this->assertEquals(DaoQuery::DEFAUTL_PAGE_SIZE, $pageStats['pageSize']);
        $this->assertEquals(ceil($pageStats['totalRows'] / DaoQuery::DEFAUTL_PAGE_SIZE), $pageStats['totalPages']);
        $this->assertInstanceOf($this->_focus, $entities[0]);
    }
}
?>