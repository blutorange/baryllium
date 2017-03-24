<?php 
    $name = $name ?? 'markdown';
    $id = $id ?? $name;
    $required = $required ?? false;    
    $placeholder = $placeholder ?? '';
    $label = $label ?? 'label';
    $rows = $rows ?? 10;
?>
<div class="form-group">
    <label class="control-label" for="<?=$this->e($id)?>">
        <?= $this->e($this->egettext($label)) ?>
        <?php if ($required): ?><span class="required-star"> *</span><?php endif; ?>
    </label>
    <textarea
        class="form-control"
        data-provide="markdown-loc"
        id="<?=$this->e($id)?>"
        name="<?=$this->e($name)?>-md"
        rows="<?=$this->e($rows)?>"
        <?php if (!empty($placeholder)): ?> placeholder="<?= $this->gettext($placeholder)?>" <?php endif; ?>
        <?php if ($required): ?> required <?php endif; ?>
    ></textarea>
    <input type="hidden" id="<?=$this->e($id)?>-hidden" name="<?=$this->e($name)?>" />
</div>