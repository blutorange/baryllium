<?php
    use League\Plates\Template\Template;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template */
    $this->layout('portal', ['title' => 'Site Settings']);
    $this->setActiveSection(SectionBasic::$SITE_SETTINGS);
?>
<div class="container">
    <h1>TODO Seiten Einstellungen</h1>
</div>