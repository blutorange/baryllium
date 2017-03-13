<?php 
    $name = $name ?? 'checkbox';
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
        type= "checkbox">
        <?php if ($required): ?> required <?php endif; ?>
</div>