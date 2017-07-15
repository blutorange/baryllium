<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */
    $this->layout('portal', ['title' => 'Database Settings']);
    $this->setActiveSection(SectionBasic::$SITE_SETTINGS_DATABASE);
?>
<div class="container">
    <form id="settings_database_form"
      class="requires-login"
      data-bootstrap-parsley
      novalidate
      method="POST"
      action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
        
        <input type="hidden" name="submitButton"/>                
    
        <?php $this->insert('partials/component/tc_config_path', ['form' => $form]) ?>

        <fieldset>
            <legend><?=$this->egettext('settings.database.heading')?></legend>
        </fieldset>
        
    </form>
</div>