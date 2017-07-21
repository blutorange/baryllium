<?php 
    $name = $name ?? 'checkbox';
    $id = $id ?? $name;
    $required = $required ?? false;    
    $label = $label ?? 'label';
    $escapeLabel = $escapeLabel ?? true;
    $persist = $persist ?? '';
    $styleClassesInput = $styleClassesInput ?? '';
    $labelI18n = $labelI18n ?? true;
    $labelData = $labelData ?? [];
    $label = $labelI18n ? $this->gettext($label, $labelData) : $label;
    $value = $value ?? false;
    $inline = $inline ?? true;   
    if (!empty($persist)) {
        $styleClassesInput .= ' persist ';
    }
?>
<div class="form-group <?= $inline ? 'checkbox-inline' : ''?>">
    <label class="control-label" for="<?=$this->e($id)?>">
        <input
            class="form-inline <?=$styleClassesInput?>"
            id="<?=$this->e($id)?>"
            <?php if (!empty($persist)): ?>
                data-persist-type="<?=$persist?>"
                data-persist-namespace="<?=$this->e($persistNamespace ?? 'fields')?>"
                data-persist-uid="<?=$this->e($persistUid ?? 0)?>"
            <?php endif;?>
            name="<?=$this->e($name)?>"
            type="checkbox"
            <?= $value ? 'checked' : '' ?>
            value="on"
            <?php if ($required): ?>required<?php endif; ?>
        />
        <?=$escapeLabel ? $this->e($label) : $label?>
    </label>        
</div>
