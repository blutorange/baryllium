<?php
use Util\CmnCnst;

$this->layout('master', ['title' => $title ?? 'Portal'])
        
?>

<!-- Include some header -->

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?=$this->e($this->getResource(CmnCnst::PATH_DASHBOARD))?>">Moose</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      
        <ul class="nav navbar-nav">
        <li><a href="<?=$this->e($this->getResource(CmnCnst::PATH_DASHBOARD))?>">Dashboard </a></li>
        <li><a href="<?=$this->e($this->getResource(CmnCnst::PATH_FORUM))?>">Forum</a></li>
        <li><a href="<?=$this->e($this->getResource(CmnCnst::PATH_USERPROFILE))?>">Profil</a></li>
      </ul>
        
      <form class="navbar-form navbar-left">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="#">Link</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#">Action</a></li>
            <li><a href="#">Another action</a></li>
            <li><a href="#">Something else here</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="#">Separated link</a></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

<header>
    <!-- Render messages, when there are any in the header. -->
    <?php
        if (isset($messages) && sizeof($messages) > 0) {
            $this->insert('partials/messages', ['messages' => $messages]);
        }
    ?>
</header>

<div id="wrapper">


    <!-- Page Content -->
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                   <!-- <a href="#menu-toggle" class="btn btn-default" id="menu-toggle">Toggle Menu</a> -->
                    <?=$this->section('content')?>
                </div>
            </div>
        </div>
    </div>
    <!-- /#page-content-wrapper -->

</div>
<!-- /#wrapper -->

<!-- Include some footer -->
<footer class="footer">
    <div class="container">
        <p class="text-muted"><?=$this->egettext('portal.footer.text')?></p>
    </div>
</footer>

<!-- Menu Toggle Script -->
<script>
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
</script>