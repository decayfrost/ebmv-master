<?php
/**
 * Test case for Utils - UDate
 *
 * @package    Test
 * @subpackage Core
 * @since      2012-09-01
 * @author     lhe<helin16@gmail.com>
 *
 */
class UDateUnitTest extends CoreUtilsUnitTestAbstract
{
    /**
     * The UDate object that we are testing
     * 
     * @var UDate
     */
    private $_uDate;
    /**
     * pre-test for each test function
     */
    public function setUp()
    {
        parent::setUp();
        $this->_uDate = new UDate();
    }
    /**
     * post test for each test function
     */
    public function tearDown()
    {
        $this->_uDate = null;
        parent::tearDown();
    }
    /**
     * testing the UDate::__toString(), UDate::getDateTime() function
     */
    public function testToString()
    {
        $this->_uDate = new UDate('2010-01-01 00:00:00');
        $now = date_create('2010-01-01 00:00:00');
        $this->assertEquals($now->format('Y-m-d H:i:s'), $this->_uDate->__toString());
        $this->assertEquals($now, $this->_uDate->getDateTime());
        
        $this->_uDate = new UDate('2010-01-01 00:00:00asfd');
        $this->assertEquals(UDate::zeroDate(), $this->_uDate->__toString());
    }
    /**
     * testing the UDate::zeroDate() function
     */
    public function testZeroDate()
    {
        $date = new UDate();
	    $date->setDate(1, 1, 1);
	    $date->setTime(0, 0, 0);
        $this->assertEquals($date, UDate::zeroDate());
    }
    /**
     * testing the UDate::setTimeZone() and UDate::getTimeZone() function
     */
    public function testGSTimeZone()
    {
        $timeZone = "UTC";
        $this->_uDate->setTimeZone($timeZone);
        $this->assertEquals(new DateTimeZone($timeZone), $this->_uDate->getTimeZone());
    }
    /**
     * testing the UDate::before(), UDate::after(), UDate::beforeOrEqualTo(), UDate::afterOrEqualTo(), UDate::notEqual() and UDate::equal() function
     */
    public function testBefore()
    {
        $this->_uDate->modify('-1 day');
        $now = new UDate();
        $this->assertTrue($this->_uDate->before($now));
        $this->assertTrue($this->_uDate->beforeOrEqualTo($now));
        $this->assertTrue($this->_uDate->notEqual($now));
        $this->assertFalse($this->_uDate->after($now));
        $this->assertFalse($this->_uDate->afterOrEqualTo($now));
        $this->assertFalse($this->_uDate->equal($now));
    }
}
?>