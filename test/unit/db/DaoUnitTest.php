<?php
/**
 * Test case for Dao - Dao
 *
 * @package    Test
 * @subpackage Core
 * @since      2012-09-01
 * @author     lhe<helin16@gmail.com>
 *
 */
class DaoUnitTest extends CoreDaoUnitTestAbstract
{
    /**
     * testing the Dao::getSingleResultNative() function
     */
    public function testGetSingleResultNative()
    {
        $sql = "select 1";
        Dao::beginTransaction();
        $result = Dao::getSingleResultNative($sql);
        $this->assertEquals(1, count($result));
        Dao::commitTransaction();
    }
    /**
     * testing the Dao::getResultsNative() function
     */
    public function testGetResultsNative()
    {
        $sql = "select 1";
        Dao::beginTransaction();
        $result = Dao::getResultsNative($sql);
        $this->assertGreaterThanOrEqual(1, count($result));
        Dao::rollbackTransaction();
    }
    /**
     * testing the Dao::execSql() function
     */
    public function testExecSql()
    {
        $sql = "select ?";
        $lastId = null;
        $stmt = Dao::execSql($sql, array(1), $lastId);
        $this->assertInstanceOf('PDOStatement', $stmt);
    }
}
?>