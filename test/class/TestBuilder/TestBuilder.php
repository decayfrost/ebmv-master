<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';   
/**
 * This is a simple test case builder, which just copy the structure of the entities and convert them into test cases
 * 
 * @package    Test
 * @subpackage class
 * @author     lhe<helin16@gmail.com>
 * @since      2012-09-01
 */
class TestBuilder
{
    /**
     * Testrunner
     * 
     * @var TestRunner
     */
    private $_testRunner;
    /**
     * What the test builder is building for
     * 
     * @var string
     */
    private $_buildFor = self::BUILD_FOR_ENTITY;
    /**
     * BUILD_FOR_ENTITY
     * 
     * @var string
     */
    const BUILD_FOR_ENTITY = 'Entity';
    /**
     * BUILD_FOR_SERVICE
     * 
     * @var string
     */
    const BUILD_FOR_SERVICE = 'Service';
    /**
     * BUILD_FOR_UTILS
     * 
     * @var string
     */
    const BUILD_FOR_UTILS = 'Utils';
    /**
     * BUILD_FOR_DAO
     * 
     * @var string
     */
    const BUILD_FOR_DAO = 'Dao';
    /**
     * building the entity test cases
     * 
     * @param string $srcPath    The test cause files are created from where
     * @param string $targetPath The target to where we are copies these test case files to 
     * 
     * @return TestBuilder
     */
    public function buildTestCase($srcPath, $targetPath, $buildFor = self::BUILD_FOR_ENTITY)
    {
        $this->_buildFor = $buildFor;
        $this->_testRunner = new TestRunner($srcPath, array(), false);
        $fileTree = $this->_testRunner->getFileTree();
        $this->_buildTestCase($fileTree, $srcPath, $targetPath);
        return $this;
    }
    /**
     * Creator for the testcase files
     * 
     * @param string $fileTree   The file tree
     * @param string $srcPath    The test cause files are created from where
     * @param string $targetPath The target to where we are copies these test case files to 
     * 
     * @return TestBuilder
     */
    private function _buildTestCase($fileTree, $srcPath, $targetPath)
    {
        foreach($fileTree as $file => $fileInfo)
        {
            //if we hit a php file
            if(strstr($file, '.php') !== false)
            {
                $className = str_replace(".php", "", $file);
                $filePath = str_replace($srcPath, $targetPath, str_replace($file, $className . "UnitTest.php", $fileInfo));
                if (file_exists($filePath) === false)
                {
                    $fileTokens = explode("/", $filePath);
                    array_pop($fileTokens);
                    $dir = implode("/", $fileTokens);
                    if(!file_exists($dir) || !is_dir($dir))
                    {
                        mkdir($dir);
                    }
                    $fileContent = str_replace('{className}', $className, $this->_getTemplate(ucfirst($this->_buildFor) ));
                    $written = file_put_contents($filePath, $fileContent);
                }
            }
            else
            {
                $this->_buildTestCase($fileInfo, $srcPath, $targetPath);
            }
        }
        return $this;
    }
    /**
     * Getting the Template
     * 
     * @return string
     */
    private function _getTemplate($buildFor)
    {
        return "<?php
/**
 * Test case for $buildFor - {className}
 *
 * @package    Test
 * @subpackage Core
 * @since      2012-09-01
 * @author     lhe<helin16@gmail.com>
 *
 */
class {className}UnitTest extends Core". $buildFor . "UnitTestAbstract
{
   /**
     * The testing " . strtolower($buildFor) . " class name
     *
     * @var string
     */
    protected \$_" . strtolower($buildFor) . "Name = '{className}';
    /**
     * testing the __toString function
     */
    public function testToString()
    {
       //TODO: need to test __toString()
    }
}
?>";
    }
}
$tb = new TestBuilder();
$tb->buildTestCase(dirname(__FILE__) . '/../../../Core/Entity', 
        dirname(__FILE__) . '/../../core/unit/entity', 
        TestBuilder::BUILD_FOR_ENTITY)
    ->buildTestCase(dirname(__FILE__) . '/../../../Core/Services', 
        dirname(__FILE__) . '/../../core/unit/services', 
        TestBuilder::BUILD_FOR_SERVICE)
    ->buildTestCase(dirname(__FILE__) . '/../../../Core/Dao', 
        dirname(__FILE__) . '/../../core/unit/Dao', 
        TestBuilder::BUILD_FOR_DAO)
    ->buildTestCase(dirname(__FILE__) . '/../../../Core/Utils', 
        dirname(__FILE__) . '/../../core/unit/utils', 
        TestBuilder::BUILD_FOR_UTILS);
