<?php 
    $name = $name ?? 'input';
    $id = $id ?? $name;
    $required = $required ?? false;    
    $minlength = $minlength ?? 0;
    $maxlength = $maxlength ?? 0;
    $type = $type ?? 'input';
    $placeholder = $placeholder ?? '';
    $placeholderI18n = $placeholderI18n ?? true;
    $label = $label ?? 'label';
    $pattern = $pattern ?? '';
    $patternMessage = $patternMessage ?? '';
    $remote = $remote ?? '';
    $remoteMessage = $remoteMessage ?? '';
    $equalto = $equalto ?? '';
    $equaltoMessage = $equaltoMessage ?? '';
    $help = $help ?? '';
    $value = $value ?? '';
    $min = $min ?? null;
    $max = $max ?? null;
    $labelData = $labelData ?? [];
    $escapeLabel = $escapeLabel ?? true;
?>
<div class="form-group">
    <label class="control-label" for="<?=$this->e($id)?>">
        <?= $escapeLabel ? $this->egettext($label, $labelData) : $this->gettext($label, $labelData) ?>
        <?php if ($required): ?><span class="required-star"> *</span><?php endif; ?>
    </label>
    <div class="<?=$type === 'password' ? 'input-group' : ''?> add-on">
        <input
            class="form-control"
            id="<?=$this->e($id)?>"
            name="<?=$this->e($name)?>"
            type="<?=$this->e($type)?>"
            value="<?=$this->e($value)?>"
            <?php if (!empty($min)): ?> min="<?=$min?>" <?php endif; ?>
            <?php if (!empty($max)): ?> max="<?=$max?>" <?php endif; ?>
            <?php if (!empty($equalto)): ?> data-parsley-equalto="<?=$this->e($equalto)?>" <?php endif; ?>
            <?php if (!empty($equaltoMessage)): ?> data-parsley-equalto-message="<?=$this->egettext($equaltoMessage)?>" <?php endif; ?>
            <?php if (!empty($pattern)): ?> pattern="<?=$this->e($pattern)?>"<?php endif; ?>
            <?php if (!empty($patternMessage)): ?> data-parsley-pattern-message="<?=$this->egettext($patternMessage)?>" <?php endif; ?>
            <?php if (!empty($placeholder)): ?> placeholder="<?= $placeholderI18n ? $this->egettext($placeholder) : $this->e($placeholder)?>" <?php endif; ?>
            <?php if (!empty($remote)): ?> data-parsley-remote="<?= $this->e($remote)?>" <?php endif; ?>
            <?php if (!empty($remoteMessage)): ?> data-parsley-remote-message="<?= $this->egettext($remoteMessage)?>" <?php endif; ?>        
            <?php if ($required): ?> required <?php endif; ?>
            <?php if ($minlength > 0): ?> minlength="<?=$this->e($minlength)?>" <?php endif; ?>
            <?php if ($maxlength > 0): ?> maxlength="<?=$this->e($maxlength)?>" <?php endif; ?>
            <?php if (!empty($help)): ?>aria-describedby="<?=$this->e($id)?>-helpBlock"<?php endif; ?>
        />
        <?php if ($type === 'password'): ?>
            <div class="input-group-btn">
                <button class="btn btn-default pw-trigger" data-pw-trigger-id="<?=$this->e($id)?>" type="button" title="<?=$this->egettext('form.password.hideshow')?>">
                    <span class="glyphicon glyphicon-eye-open"></span>
                </button>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($help)): ?>
        <span id="<?=$this->e($id)?>-helpBlock" class="help-block"><?= $this->egettext($help)?></span>
    <?php endif; ?>
</div>