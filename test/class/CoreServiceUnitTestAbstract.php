<?php
/**
 *
 * Abstract Core Unit Test for Services
 *
 * @package    Test
 * @subpackage class
 * @author     lhe<helin16@gmail.com>
 * @since      2012-09-01
 *
 */
abstract class CoreServiceUnitTestAbstract extends CoreUnitTestAbstract
{
    /**
     * The testing service's class name
     * 
     * @var string
     */
    protected $_serviceName = '';
    /**
     * BaseService object to test
     *
     * @var BaseServiceAbastract
     */
    protected $_serviceObj;
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
    }
    /**
     * (non-PHPdoc)
     * @see CoreUnitTestAbstract::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $this->_serviceObj = new $this->_serviceName();
    }
    /**
     * (non-PHPdoc)
     * @see CoreUnitTestAbstract::tearDown()
     */
    public function tearDown()
    {
        $this->_serviceObj = null;
        parent::tearDown();
    }
}