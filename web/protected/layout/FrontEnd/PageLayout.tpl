<!DOCTYPE html>
<html lang="en">
<com:THead ID="titleHeader" Title="<%= Core::getLibrary()->getName() %> - BMV eResource Interface">
    <meta charset="UTF-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<meta name="description" content="">
	<meta name="keywords" content="">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
	<!-- Optional theme -->
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
	<!-- Latest compiled and minified JavaScript -->
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	
	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="//oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="//oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    
	<script type="text/javascript">
	/*<![CDATA[*/
		jQuery.noConflict();
	/*]]>*/
	</script>
</com:THead>
<body role="document">
    <div id="frontend">
	    <com:TForm>
	       <div class="framewrapper header">
	           <div class="contentwrapper">
	            <com:Application.layout.FrontEnd.Header.Header ID="FrontEndHeader" />
	           </div>
	       </div>
	       <div class="framewrapper container mainbody">
	           <div class="contentwrapper">
	              <com:TContentPlaceHolder ID="MainContent" />
	           </div>
	       </div>
	    </com:TForm>
    </div>
    <div class="framewrapper footer">
        <div class="contentwrapper">
            <com:Application.layout.FrontEnd.Footer.Footer ID="FrontEndFooter" />
        </div>
    </div>
</body>
</html>