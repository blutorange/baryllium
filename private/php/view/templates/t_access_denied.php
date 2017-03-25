<?php $this->layout('portal') ?>

<div id="access-denied">
    <a href="<?=$this->e($this->getResource(Util\CmnCnst::PATH_DASHBOARD))?>">
        <?=$this->egettext('accessdenied.backto.dashboard')?>
    </a>
</div>