<?php
/**
 *
 * Abstract Core Unit Test for Dao
 *
 * @package    Test
 * @subpackage class
 * @author     lhe<helin16@gmail.com>
 * @since      2012-09-01
 *
 */
abstract class CoreDaoUnitTestAbstract extends CoreUnitTestAbstract
{
    /**
     * The focus class
     * @var string
     */
    protected $_focus = 'Person';
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
}