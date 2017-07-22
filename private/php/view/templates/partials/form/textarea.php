<?php 
    $name = $name ?? 'textarea';
    $id = $id ?? $name;
    $required = $required ?? false;    
    $minlength = $minlength ?? 0;
    $maxlength = $maxlength ?? 0;
    $placeholder = $placeholder ?? '';
    $placeholderI18n = $placeholderI18n ?? true;
    $label = $label ?? 'label';
    $remote = $remote ?? '';
    $remoteMessage = $remoteMessage ?? '';
    $help = $help ?? '';
    $persist = $persist ?? '';
    $min = empty($min) ? null : \intval($min);
    $max = empty($max) ? null : \intval($max);
    $value = $value ?? '';
    $labelI18n = $labelI18n ?? true;
    $labelData = $labelData ?? [];
    $escapeLabel = $escapeLabel ?? true;
    $label = $labelI18n ? $this->gettext($label, $labelData) : $label;
    $rows = $rows ?? 5;
    $styleClassesTextarea = '';
    if (!empty($persist)) {
        $styleClassesTextarea .= ' persist ';
    }
?>
        
<div class="form-group">
    <label class="control-label" for="<?=$this->e($id)?>">
        <?= $escapeLabel ? $this->e($label) : $label ?>
        <?php if ($required): ?><span class="required-star"> *</span><?php endif; ?>
    </label>
    <div class="add-on">
        <textarea
            class="form-control <?=$styleClassesTextarea?>"
            id="<?=$this->e($id)?>"
            name="<?=$this->e($name)?>"
            rows="<?=$this->e($rows)?>"
            <?php if (!empty($persist)): ?>
                data-persist-type="<?=$persist?>"
                data-persist-namespace="<?=$this->e($persistNamespace ?? 'fields')?>"
                data-persist-uid="<?=$this->e($persistUid ?? 0)?>"
            <?php endif;?>
            <?php if (!empty($min)): ?> min="<?=$min?>" <?php endif; ?>
            <?php if (!empty($max)): ?> max="<?=$max?>" <?php endif; ?>
            <?php if (!empty($placeholder)): ?> placeholder="<?= $placeholderI18n ? $this->egettext($placeholder) : $this->e($placeholder)?>" <?php endif; ?>
            <?php if (!empty($remote)): ?> data-parsley-remote="<?= $this->e($remote)?>" <?php endif; ?>
            <?php if (!empty($remoteMessage)): ?> data-parsley-remote-message="<?= $this->egettext($remoteMessage)?>" <?php endif; ?>        
            <?php if ($required): ?> required <?php endif; ?>
            <?php if ($minlength > 0): ?> minlength="<?=$this->e($minlength)?>" <?php endif; ?>
            <?php if ($maxlength > 0): ?> maxlength="<?=$this->e($maxlength)?>" <?php endif; ?>
            <?php if (!empty($help)): ?>aria-describedby="<?=$this->e($id)?>-helpBlock"<?php endif; ?>
        ><?=$this->e($value)?></textarea>
    </div>
    <?php if (!empty($help)): ?>
        <span id="<?=$this->e($id)?>-helpBlock" class="help-block"><?= $this->egettext($help)?></span>
    <?php endif; ?>
</div>