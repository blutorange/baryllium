<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */
    $this->layout('portal', ['title' => 'Site Settings']);
    $this->setActiveSection(SectionBasic::$SITE_SETTINGS_DATABASE);
?>
<div class="container">
    <fieldset>
        <legend><?=$this->egettext('settings.database.heading')?></legend>
    </fieldset>
</div>