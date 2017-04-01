<?php $this->layout('portal') ?>

<div id="access-denied">
    <a href="<?=$this->e($this->getResource(Moose\Util\CmnCnst::PATH_DASHBOARD))?>">
        <?=$this->egettext('accessdenied.backto.dashboard')?>
    </a>
</div>