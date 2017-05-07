<?php
    use Moose\ViewModel\SectionBasic;
    $this->layout('portal');
    $this->setActiveSection(SectionBasic::$LEGALESE);
?>

<h1><?=$this->egettext('legalese.terms')?></h1>

<?=$this->insert('partials/agb')?>