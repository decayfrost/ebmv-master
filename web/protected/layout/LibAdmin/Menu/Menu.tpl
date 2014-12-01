<div role="navigation" class="navbar navbar-default navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <button data-target=".navbar-collapse" data-toggle="collapse" class="navbar-toggle" type="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a href="/libadmin/" class="navbar-brand" title="Dashbaord for library admin of <%= Core::getLibrary()->getName() %>">Library Admin</a>
    </div>
    <div class="navbar-collapse collapse">
      <ul class="nav navbar-nav">
        <%=$this->getMenu() %>
      </ul>
      <ul class="nav navbar-nav navbar-right">
         <li><a href="/logout.html?url=/libadmin/me.html">Welcome, <%= Core::getUser()->getPerson() %></a></li>
         <li><a href="/logout.html?url=/libadmin/">Logout</a></li>
      </ul>
    </div><!--/.nav-collapse -->
  </div>
</div>