<com:Application.controls.FancyBox.FancyBox />
<nav class="navbar navbar-default navbar-static-top" role="navigation">
	<div class="container">
		<div class="row">
			<div class="col-sm-8">
				<div class="media">
				  <a class="pull-left logo" href="/">
				    <img class="media-object" src="/themes/<%= $this->getPage()->getTheme()->getName() %>/images/logo.png" alt="<%= Core::getLibrary()->getName() %>">
				  </a>
				  <div class="media-body title">
				    <h4 ><%= Core::getLibrary()->getName() %></h4>
				  </div>
				</div>
			</div>
			<div class="col-sm-4 hidden-xs topmenu">
				<ul class="nav navbar-nav navbar-right">
					<li><a href="/help.html" class="iconbtn"><div class="btnname">帮助/幫助<small>Help</small></div><span class="glyphicon glyphicon-question-sign"></span></a></li>
					<li>
						<a href="/user.html" class="iconbtn">
							<%= Core::getUser() instanceof UserAccount ?
								'<div class="btnname">Welcome<small>' . Core::getUser()->getPerson() . '</small></div>'
								:
								'<div class="btnname">登录/登錄<small>Login</small></div>'
							%>
							<span class="glyphicon glyphicon-user">
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="container mainmenu">
		<div class="navbar-header">
			<button class="navbar-toggle" data-target="#topmenulist" data-toggle="collapse" type="button">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
		<div class="collapse navbar-collapse" id="topmenulist">
			<ul class="nav navbar-nav">
				<li class='hidden-sm'>
				    <a href="/" class="iconbtn "><div class="btnname">首页/首頁<small>Home</small></div><span class="glyphicon glyphicon-home"></span></a>
				</li>
				<li><a href="/user.html" class="iconbtn"><div class="btnname">我的书架/我的書架<small>My Bookshelf</small></div><span class="glyphicon glyphicon-signal"></span></a></li>
				<li class="hidden-xs"><a> | </a></li>
				<li class="dropdown visible-lg visible-md visible-sm visible-xs">
				    <a href="#" role="button" class="dropdown-toggle iconbtn" data-toggle="dropdown" id="schinese-dropdown-btn" data-target="#">
				        <div class="btnname">简体中文<small>Simplifed Chinese</small></div>
				        <b class="caret"></b>
				    </a>
				    <ul class="dropdown-menu" role="menu" aria-labelledby="schinese-dropdown-btn">
						<li>
						  <a href="/products/1/1" class="iconbtn">
						      <div class="row">
							      <div class="col-xs-4">书</div>
							      <div class="col-xs-8 en">Books</div>
						      </div>
						  </a>
						</li>
						<li>
						  <a href="/products/1/3" class="iconbtn">
						      <div class="row">
							      <div class="col-xs-4">杂志</div>
							      <div class="col-xs-8 en">Magazines</div>
						      </div>
						  </a>
						</li>
						<li>
						  <a href="/products/1/2" class="iconbtn">
						      <div class="row">
							      <div class="col-xs-4">报纸</div>
							      <div class="col-xs-8 en">NewsPapers</div>
						      </div>
						  </a>
						</li>
					</ul>
				</li>
				<li class="dropdown visible-lg visible-md visible-sm visible-xs">
                    <a href="#" class="iconbtn" data-toggle="dropdown" id="tchinese-dropdown-btn">
                        <div class="btnname">繁體中文<small>Traditional Chinese</small></div>
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu" role="menu"  aria-labelledby="tchinese-dropdown-btn">
                        <li>
                          <a href="/products/2/1" class="iconbtn">
                              <div class="row">
                                  <div class="col-xs-4">書</div>
                                  <div class="col-xs-8 en">Books</div>
                              </div>
                          </a>
                        </li>
                        <li>
                          <a href="/products/2/3" class="iconbtn">
                              <div class="row">
                                  <div class="col-xs-4">雜誌</div>
                                  <div class="col-xs-8 en">Magazines</div>
                              </div>
                          </a>
                        </li>
                        <li>
                          <a href="/products/2/2" class="iconbtn">
                              <div class="row">
                                  <div class="col-xs-4">報紙</div>
                                  <div class="col-xs-8 en">NewsPapers</div>
                              </div>
                          </a>
                        </li>
                    </ul>
                </li>
				<li class="dropdown visible-lg visible-md visible-sm visible-xs">
                    <a href="/products/1/4" class="iconbtn" data-toggle="dropdown" id="len-dropdown-btn">
                        <div class="btnname">学英语<small>ESL</small></div>
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu extra-long" role="menu"  aria-labelledby="lch-dropdown-btn">
                        <li role="presentation">
                          <a href="/product/62" role="menuitem" class="iconbtn">
                              <div class="row">
                                  <div class="col-xs-12">新概念英语一册</div>
                              </div>
                          </a>
                        </li>
                        <li role="presentation">
                          <a href="/product/61" role="menuitem" class="iconbtn">
                              <div class="row">
                                  <div class="col-xs-12">托福词汇课</div>
                              </div>
                          </a>
                        </li>
                    </ul>
                </li>
				<li class="dropdown visible-lg visible-md visible-sm visible-xs">
                    <a href="#" class="iconbtn" data-toggle="dropdown" id="lch-dropdown-btn">
                        <div class="btnname">学汉语<small>Learn Chinese</small></div>
                        <b class="caret"></b>
                        <ul class="dropdown-menu extra-long" role="menu" aria-labelledby="lch-dropdown-btn">
                        
                        </ul>
                    </a>
                    <ul class="dropdown-menu extra-long" role="menu"  aria-labelledby="lch-dropdown-btn" id="learn-chinese-menu">
                    </ul>
                </li>
				<li class="visible-xs"><a href="/" class="iconbtn"><div class="btnname">帮助/幫助<small>Help</small></div><span class=" glyphicon glyphicon-question-sign"></span></a></li>
				<li class="visible-xs"><a href="/user.html" class="iconbtn"><div class="btnname">登录/登錄<small>Login</small></div></a></li>
			</ul>
		</div>
	</div>
</nav>


