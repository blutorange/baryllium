<?php 
    $name = $name ?? 'checkbox';
    $id = $id ?? $name;
    $required = $required ?? false;    
    $label = $label ?? 'label';
    $escapeLabel = $escapeLabel ?? true;
    $persist = $persist ?? '';
    $styleClassesInput = $styleClassesInput ?? '';
    
    if (!empty($persist)) {
        $styleClassesInput .= ' persist';
    }
?>
<div class="form-group checkbox-inline">
    <label class="control-label" for="<?=$this->e($id)?>">
        <input
            class="form-inline <?=$styleClassesInput?>"
            id="<?=$this->e($id)?>"
            data-persist-type="<?=$persist?>"
            name="<?=$this->e($name)?>"
            type="checkbox"
            value="on"
            <?php if ($required): ?>required<?php endif; ?>
        />
        <?=$escapeLabel ? $this->egettext($label) : $this->gettext($label)?>
    </label>
        
</div>