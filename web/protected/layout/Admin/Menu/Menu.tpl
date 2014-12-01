<div role="navigation" class="navbar navbar-inverse navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <button data-target=".navbar-collapse" data-toggle="collapse" class="navbar-toggle" type="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a href="/admin/" class="navbar-brand"><%= Core::getLibrary()->getName() %></a>
    </div>
    <div class="navbar-collapse collapse">
      <ul class="nav navbar-nav">
        <%=$this->getMenu() %>
      </ul>
    </div><!--/.nav-collapse -->
  </div>
</div>