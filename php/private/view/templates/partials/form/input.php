<?php 
    $name = $name ?? 'input';
    $id = $id ?? $name;
    $required = $required ?? false;    
    $minlength = $minlength ?? 0;
    $maxlength = $maxlength ?? 0;
    $mask = $mask?? false;
    $placeholder = $placeholder ?? '';
    $label = $label ?? 'label';
    $remote = $remote ?? '';
    $remoteMessage = $remoteMessage ?? '';
?>
<div class="form-group">
    <label class="control-label" for="<?=$this->e($id)?>"><?= $label ?></label>
    <input
        class="form-control"
        id="<?=$this->e($id)?>"
        name="<?=$this->e($name)?>"
        type= <?= $mask ? 'password' : 'input' ?>
        <?php if (!empty($placeholder)): ?> placeholder="<?= $this->e($placeholder)?>" <?php endif; ?>
        <?php if (!empty($remote)): ?> data-parsley-remote="<?= $this->e($remote)?>" <?php endif; ?>
        <?php if (!empty($remoteMessage)): ?> data-parsley-remote-message="<?= $this->e($remoteMessage)?>" <?php endif; ?>        
        <?php if ($required): ?> required <?php endif; ?>
        <?php if ($minlength > 0): ?> minlength="<?=$this->e($minlength)?>" <?php endif; ?>
        <?php if ($maxlength > 0): ?> maxlength="<?=$this->e($maxlength)?>" <?php endif; ?>
    />
</div>