<!DOCTYPE html>
<html id="home" lang="en">
    <head>
        <meta charset=utf-8 />
        <title>Core Test</title>
        <link rel="stylesheet" type="text/css" href="class/resources/css/testrunner.css" />
        <script type="text/javascript" src="class/resources/js/jquery-1.8.1.min.js"></script>
	</head>
	<body>
        <?php
            require dirname(__FILE__) . '/bootstrap.php';
            $testRunner = new TestRunner(dirname(__FILE__), array('class', 'index.php', 'bootstrap.php', 'PHPUnitReport'), true, false);
            if (isset($_REQUEST['testpath']) && ($testPath = trim($_REQUEST['testpath'])) !== '') {
                $testRunner->run($testPath);
            } else {
                echo $testRunner->getFileTree();
            }
        ?>
     </body>
</html>