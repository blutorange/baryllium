<?php
    use League\Plates\Template\Template;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template */
    $this->layout('portal');
    $this->setActiveSection(SectionBasic::$LOGIN);
?>
<a href="<?=$this->e($redirectUrl)?>"><?= $this->egettext('login.redirect')?></a>