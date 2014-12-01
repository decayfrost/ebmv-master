<?php
/**
 *
 * PHP unit test suite
 *
 * @package    Core
 * @subpackage Test
 * @since      2012-09-01
 * @author     lhe<helin16@gmail.com>
 *
 */
class TestRunner
{
    /**
     * The excluding files on the tree
     * 
     * @var string[]
     */
    private $_excludeFiles = array('.', '..', '.svn', 'index.php', 'testrunner', 'bootstrap.php', 'run.php', 'commandline_run.php');
    /**
     * The root path of all test cases
     * 
     * @var string
     */
    private $_rootPath = '';
    /**
     * Are we running this in HTML mode
     * @var bool
     */
    private $_htmlMode = true;
    /**
     * whether we need to generate coverage report
     *
     * @var bool
     */
    private $_genCoverageReport = false;
    /**
     * where the coverage report will be saved to 
     * 
     * @var string
     */
    private $_coverageReportPath = '/code-coverage-report';
    /**
     * constructor
     * 
     * @param string $rootPath          The root path of the test cases
     * @param array  $exclFiles         The additional excluding files on the tree
     * @param bool   $genCoverageReport Whether we want to generate the code coverage report when we are running the tests
     */
    public function __construct($rootPath, $exclFiles = array(), $htmlMode = true, $genCoverageReport = false) 
    {
        $this->_rootPath = $rootPath;
        $this->_excludeFiles = array_merge($this->_excludeFiles, $exclFiles);
        $this->_htmlMode = ($htmlMode === true ? true : false);
        $this->_genCoverageReport = $genCoverageReport;
    }
    /**
     * Getting the test case file tree
     * 
     * @param Mixed  $tree         The multi-level file tree
     * @param string $previousPath The previouse file path
     * 
     * @return string The html code of the file tree
     */
    public function getFileTree($tree = null, $previousPath = DIRECTORY_SEPARATOR) 
    {
        if ($tree === null) 
            $tree = $this->_getFileTree($this->_rootPath);
        if ($this->_htmlMode !== true) 
            return $tree;
        if(is_array($tree) !== true || count($tree) === 0)
            return '';
        $html = '<ul>';
        foreach($tree as $key => $info) 
        {
            $html .= '<li><a href="?testpath=' . $this->_rootPath . $previousPath . $key . '">' . str_replace('.php', '', $key) . '</a></li>';
            if(is_array($info) === true && count($info) > 0) 
            {
                $html .= $this->getFileTree($info, $previousPath . $key . DIRECTORY_SEPARATOR);
            }
        }
        $html .= '</ul>';
        return $html;
    }
    /**
     * Getting the file tree for that path
     * 
     * @param string $path The path of that file level
     * 
     * @return Mixed The multi-leveled file tree
     */
    private function _getFileTree($path) 
    {
        $treeArray = array();
        if ($handle = opendir($path)) 
        {
            while (false !== ($entry = readdir($handle))) 
            {
                if (!in_array($entry, $this->_excludeFiles)) 
                {
                    $filePath = $path . DIRECTORY_SEPARATOR . $entry;
                    //if this is a directory
                    if (is_dir($filePath)) 
                    { 
                        $treeArray[$entry] = $this->_getFileTree($filePath);
                    } 
                    //if this is a file
                    else if (is_file($filePath)) 
                    { 
                        $treeArray[$entry] = $filePath;
                    }
                }
            }
        }
        return $treeArray;
    }
    /**
     * Actioner: runing the tests
     * 
     * @param string $path The path of the tests
     * 
     * @throws Exception
     */
    public function run($path) 
    {
        if ($this->_genCoverageReport === true) 
        {
            //exclude Framework
            $filter = new PHP_CodeCoverage_Filter();
            $filter->addDirectoryToBlacklist( dirname(__FILE__) . '/../../Core/Framework/');
            $coverage = new PHP_CodeCoverage(null, $filter);
            $coverage->start('TestReport');
        }
//         $this->_findEmptyTables();
        $this->_testAll($path);
        //if we started the PHP coverage already
        if ($coverage instanceof PHP_CodeCoverage) 
        {
            $coverage->stop();
            $writer = new PHP_CodeCoverage_Report_HTML;
            $writer->process($coverage, dirname(__FILE__) . $this->_coverageReportPath);
        }
    }
    /**
     * executor for all the test cases
     * 
     * @param string $path The path to all test cases
     * 
     * @throws Exception
     * @return TestRunner
     */
    private function _testAll($path)
    {
        try
        {
            //getting which tree node is selected
            $tree = $this->_getFileTree($this->_rootPath);
            $testNode = $tree;
            $paths = array_filter(explode(DIRECTORY_SEPARATOR, str_replace($this->_rootPath, '', $path)));
            foreach ($paths as $node)
            {
                if (isset($testNode[$node]))
                {
                    $testNode = $testNode[$node];
                }
            }
            //getting all testcases for the unittest
            $testCases = array();
            $fileArray = $this->_flatFileTree($testNode);
            foreach($fileArray as $file)
            {
                //if we can't even find the test case file
                if (!file_exists($file))
                {
                    continue;
                }
                require_once $file;
                $testCaseName = explode(DIRECTORY_SEPARATOR, $file);
                $testCaseName = end($testCaseName);
                $testCaseName = str_replace('.php', '', $testCaseName);
                $testCase = new $testCaseName();
                if ($testCase instanceof PHPUnit_Framework_TestCase)
                {
                    $testCases[$file] = $testCaseName;
                }
            }
            if (count($testCases) > 0)
            {
                $suite = new PHPUnit_Framework_TestSuite(get_class($this) . 'suite');
                foreach($testCases as $path => $testClass)
                {
                    $suite->addTestSuite($testClass);
                }
                $result = new PHPUnit_Framework_TestResult();
                $suite->run($result);
                echo $this->_displayResult($path, $result);
            }
            else
            {
                throw new Exception('No test cases to run!');
            }
        }
        catch (Exception $e)
        {
            echo $this->_drawFancyError($e);
        }
        return $this;
    }
    /**
     * Finding whether there are empty tables
     * 
     * @return TestRunner
     */
    private function _findEmptyTables()
    {
        try
        {
            $sql = "select table_schema, table_name from information_schema.tables where table_schema = 'hydra' and table_rows = 0";
            $result = Dao::getResultsNative($sql, array(), PDO::FETCH_ASSOC);
            if (count($result) === 0)
            {
                throw new Exception("We've found some empty tables: " . implode(", ", array_map(create_function('$a', 'return $a["table_schema"] . "." . $a["table_name"];'), $result)));
            }
        }
        catch(Exception $ex)
        {
            echo $this->_drawFancyError($ex);
        }
        return $this;
    }
    /**
     * Displaying the result in the well formatted form
     * 
     * @param string                       $path   The path of all the test cases
     * @param PHPUnit_Framework_TestResult $result The test results
     * 
     * @return string
     */
    private function _displayResult($path, PHPUnit_Framework_TestResult $result) 
    {
        if ($this->_htmlMode !== true) 
        {
            echo "\n\nTests Passed: " . count($result->passed()) . "/" . $result->count()." Failures: " . $result->failureCount() ." Errors: " . $result->errorCount() . "\n\n";
            if ($result->failureCount() > 0 || $result->errorCount() > 0) 
            {
                if ($result->failureCount() > 0) 
                {
                    $html .= $this->_displayFEDiv('Failure(s)', $result->failures());
                }
                if ($result->errorCount() > 0) 
                {
                    $html .= $this->_displayFEDiv('Error(s)', $result->errors());
                }
            }
            return '';
        }
        $html = '<div class="resultWrapper">';
        $html .= '<h3 class="title">Running Test(s): <i>' . $path . '</i></h3>';
        $classForSummary = 'summaryPassed';
        if ($result->failureCount() > 0) 
        {
            $classForSummary = 'summaryFailed';
        } 
        else if ($result->errorCount() > 0) 
        {
            $classForSummary = 'summaryError';
        }
        $html .= '<div class="summary ' . $classForSummary . '">';
        $html .= '<span class="passed">Passed: ' . count($result->passed()) . ' / ' . $result->count() . '</span>';
        $html .= '<span class="failed">Failed: ' . $result->failureCount() . '</span>';
        $html .= '<span class="error">Error: ' . $result->errorCount() . '</span>';
        $html .= '</div>';
        if ($result->failureCount() > 0 || $result->errorCount() > 0) 
        {
            if ($result->failureCount() > 0) 
            {
                $html .= $this->_displayFEDiv('Failure(s)', $result->failures());
            }
            if ($result->errorCount() > 0) 
            {
                $html .= $this->_displayFEDiv('Error(s)', $result->errors());
            }
        }
        if ($this->_genCoverageReport === true)
        {
            $html .='<div class="resultDiv">';
                $html .='<a class="resultTitle" href="/class/' . $this->_coverageReportPath . '" target="__blank">View coverage report</a>';
            $html .='</div>';
        }
        $html .= '</div>';
        return $html;
    }
    /**
     * Displaying the failures and errors
     * 
     * @param string $title The title of the block
     * @param Mixed  $fEs   The error(s) or failure(s)
     * 
     * @return string
     */
    private function _displayFEDiv($title, $fEs = array()) 
    {
        if ($this->_htmlMode !== true || !is_array($fEs) || count($fEs) === 0) 
        {
            if ($this->_htmlMode !== true) 
            {
                echo $title . "\n\n";
                echo $this->_loopThroughFEs($fEs);
            }
            return '';
        }
        $html = '<div class="resultDiv">';
        $html .= '<h3 class="resultTitle" onclick="$(this).next().toggle();">(' . count($fEs) . ')' . $title . '</h3>';
        $html .= '<table class="errorResult" style="display:none;">';
        $html .= '<thead><tr><td>Test Case</td><td>Result</td></tr></thead>';
        $html .= '<tbody>';
        $html .= $this->_loopThroughFEs($fEs);
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        return $html;
    }
    /**
     * Looping throw the Failures and Errors generated on the PHPUnit_Framework_TestResult
     * 
     * @param Mixed $fEs Failures and Errors
     *  
     * @return string
     */
    private function _loopThroughFEs($fEs = array()) 
    {
        $html = '';
        foreach($fEs as $index => $fE) 
        {
            if ($this->_htmlMode === true) 
            {
                $html .= '<tr class="' . ($index % 2 === 0 ? 'item' : 'alertItem') . '" valign="top">';
                $html .= '<td>';
            }
            $html .= get_class($fE->failedTest());
            if ($fE instanceof PHPUnit_Framework_TestFailure) 
            {
                $html .= '::' . $fE->failedTest()->getName() . '()';
            }
            if ($this->_htmlMode === true) 
            {
                $html .= '</td>';
                $html .= '<td class="resultDetails">';
            }
            $html .= $this->_drawFancyError($fE->thrownException());
            if ($this->_htmlMode === true) 
            {
                $html .= '</td>';
                $html .= '</tr>';
            }
        }
        return $html;
    }
    /**
     * get the tree node to an array of filepath
     * 
     * @param Mixed $treeNode The node of the file tree
     * 
     * @return Ambigous <multitype:, multitype:unknown >
     */
    private function _flatFileTree($treeNode) 
    {
        $files = array();
        if (!is_array($treeNode)) 
        {
            $files[] = $treeNode;
        } 
        else 
        {
            foreach($treeNode as $node => $path) 
            {
                if(!is_array($path)) 
                {
                    $files[] = $path;
                } 
                else 
                {
                    $files = array_merge($files, $this->_flatFileTree($path));
                }
            }
        }
        return $files;
    }
    /**
     * Get the formatted exception message
     * 
     * @param Exception $ex The exception that we are trying to display
     * 
     * @return string The message
     */
    private function _drawFancyError(Exception $ex) 
    {
        if ($this->_htmlMode !== true)
        {
            return "\n===== Error: " . $ex->getMessage() . "\n". $ex->getTraceAsString() . "\n========== \n\n";
        }
        $html = '<div class="fancyErrorWrapper">';
        $html .= '<div class="title" onclick="$(this).next().toggle();">' . $ex->getMessage() . '</div>';
        $html .= '<div class="trace" style="display:none;">';
        foreach(explode("\n", $ex->getTraceAsString()) as $index => $row)
        {
            $html .= '<div class="item' . ($index % 2 === 0 ? '' : 'Alter') . '">' . $row . '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}