<?php 
    $name = $name ?? 'checkbox';
    $id = $id ?? $name;
    $required = $required ?? false;    
    $label = $label ?? 'label';
?>
<div class="form-group">
    <label class="control-label" for="<?=$this->e($id)?>">
        <?=$label?>
    </label>
    <input
        class="form-control"
        id="<?=$this->e($id)?>"
        name="<?=$this->e($name)?>"
        type="checkbox"
        value="on"
        <?php if ($required): ?>required<?php endif; ?>
    />
        
</div>