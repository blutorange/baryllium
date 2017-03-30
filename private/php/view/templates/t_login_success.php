<?php
    use League\Plates\Template\Template;
    use Ui\Section;
    /* @var $this Template */
    $this->layout('portal');
    $this->setActiveSection(Section::$LOGIN);
?>
<a href="<?=$this->e($redirectUrl)?>"><?= $this->egettext('login.redirect')?></a>