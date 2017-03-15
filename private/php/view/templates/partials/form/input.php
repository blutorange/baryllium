<?php 
    $name = $name ?? 'input';
    $id = $id ?? $name;
    $required = $required ?? false;    
    $minlength = $minlength ?? 0;
    $maxlength = $maxlength ?? 0;
    $type = $type ?? 'input';
    $placeholder = $placeholder ?? '';
    $label = $label ?? 'label';
    $pattern = $pattern ?? '';
    $remote = $remote ?? '';
    $remoteMessage = $remoteMessage ?? '';
    $equalto = $equalto ?? '';
    $equaltoMessage = $equaltoMessage ?? '';
?>
<div class="form-group">
    <label class="control-label" for="<?=$this->e($id)?>">
        <?= $this->e($this->gettext($label)) ?>
        <?php if ($required): ?><span class="required-star"> *</span><?php endif; ?>
    </label>
    <input
        class="form-control"
        id="<?=$this->e($id)?>"
        name="<?=$this->e($name)?>"
        type="<?=$this->e($type)?>"
        <?php if (!empty($equalto)): ?> data-parsley-equalto="<?=$this->e($equalto)?>" <?php endif; ?>
        <?php if (!empty($equaltoMessage)): ?> data-parsley-equalto-message="<?=$this->egettext($equaltoMessage)?>" <?php endif; ?>
        <?php if (!empty($pattern)): ?> pattern="<?=$this->e($pattern)?>" <?php endif; ?>
        <?php if (!empty($placeholder)): ?> placeholder="<?= $this->gettext($placeholder)?>" <?php endif; ?>
        <?php if (!empty($remote)): ?> data-parsley-remote="<?= $this->e($remote)?>" <?php endif; ?>
        <?php if (!empty($remoteMessage)): ?> data-parsley-remote-message="<?= $this->gettext($remoteMessage)?>" <?php endif; ?>        
        <?php if ($required): ?> required <?php endif; ?>
        <?php if ($minlength > 0): ?> minlength="<?=$this->e($minlength)?>" <?php endif; ?>
        <?php if ($maxlength > 0): ?> maxlength="<?=$this->e($maxlength)?>" <?php endif; ?>
    />
</div>