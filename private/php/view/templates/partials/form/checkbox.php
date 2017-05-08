<?php 
    $name = $name ?? 'checkbox';
    $id = $id ?? $name;
    $required = $required ?? false;    
    $label = $label ?? 'label';
    $escapeLabel = $escapeLabel ?? true;
    $persist = $persist ?? '';
    $styleClassesInput = $styleClassesInput ?? '';
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
            <?php if (!empty($persist)): ?>data-persist-type="<?=$persist?>"<?php endif;?>
            name="<?=$this->e($name)?>"
            type="checkbox"
            <?= $value ? 'checked' : '' ?>
            value="on"
            <?php if ($required): ?>required<?php endif; ?>
        />
        <?=$escapeLabel ? $this->egettext($label) : $this->gettext($label)?>
    </label>        
</div>
