<?php
    use Moose\ViewModel\SectionBasic;
    use Moose\Util\CmnCnst;
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    /* @var $this Template|PlatesMooseExtension */
    $this->layout('master', ['title' => $title ?? 'Portal']);
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
      <a id="navbar_moose" class="navbar-brand" href="<?=$this->e($this->getResource(CmnCnst::PATH_DASHBOARD))?>">Moose</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
            <?php $this->insert('partials/component/tc_navbar_entry', ['section' => SectionBasic::$DASHBOARD]) ?>
            <?php $this->insert('partials/component/tc_navbar_entry', ['section' => SectionBasic::$BOARD]) ?>
            <?php $this->insert('partials/component/tc_navbar_entry', ['section' => SectionBasic::$PROFILE]) ?>
        </ul>
        
      <!--form class="navbar-form navbar-left">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form-->
      <ul class="nav navbar-nav navbar-right">
        <li>
            <?php $this->insert('partials/component/tc_navbar_entry', ['section' => SectionBasic::$USERLIST]) ?>
        </li>
        <?php if ($this->getUser()->isValid()):?>
            <li>
                <a id="logout" href="<?=$this->egetResource(CmnCnst::PATH_LOGOUT)?>">
                        <?= $this->egettext('navigation.logout')?>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($this->getUser()->getIsSiteAdmin()): ?>
            <li class="dropdown">
                <?php $this->insert('partials/component/tc_navbar_entry_dropdown', [
                    'section' => SectionBasic::$ADMINISTRATION,
                    'items' => [
                        $this->fetch('partials/component/tc_navbar_entry', ['section' => SectionBasic::$SITE_SETTINGS]),
                        '<li role="separator" class="divider"></li>',
                        $this->fetch('partials/component/tc_navbar_entry', ['section' => SectionBasic::$IMPORT_FOS])
                    ]
                ]) ?>
            </li>
        <?php endif; ?>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

<!-- Page content -->
<div id="wrapper">
    <!-- Page Content -->
    <div id="page-content-wrapper">
        <header>
            <!-- Render messages, when there are any in the header. -->
            <?php
                if (isset($messages) && sizeof($messages) > 0) {
                    $this->insert('partials/messages', ['messages' => $messages]);
                }
            ?>
        </header>
        
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <?=$this->section('content')?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Some footer -->
<footer class="footer">
    <?php 
        include('footer.php');
    ?>
</footer>
