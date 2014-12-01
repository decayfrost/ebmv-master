<?php
/**
 *
 * Abstract Core Unit Test
 *
 * @package    Test
 * @subpackage class
 * @author     lhe<helin16@gmail.com>
 * @since      2012-09-01
 *
 */
abstract class CoreUnitTestAbstract extends PHPUnit_Framework_TestCase
{
    /**
     * Whether we running the tests in debug mode
     * 
     * @var bool
     */
    protected $_debugMode = true;
    /**
     * The total number of time that we are trying to loop when we are find a random entity
     * 
     * @var Int
     */
    const TOTAL_NO_RANDOM = 1000;
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
        if ($this->_debugMode === true) 
        {
            $content = ob_get_contents();
            ob_flush();
            echo $this->_getDebugHeader($this->getName());
            echo $content;
            echo $this->_getDebugFooter();
        }
    }
    /**
     * Getting the header div for debug div
     * 
     * @param string $funcName The name of test function
     * 
     * @return string The HTML code
     */
    private function _getDebugHeader($funcName) 
    {
        $html = '<div class="funDebugWrapper">';
        $html .= '<div class="funDebugTitle">Debugging: ' . $funcName . '</div>';
        $html .= '<div class="funDebugContent">';
        return $html;
    }
    /**
     * get the function debug information footer
     * 
     * @return string The HTML code
     */
    private function _getDebugFooter() 
    {
        $html = '</div>';
        $html .= '</div>';
        return $html;
    }
}
