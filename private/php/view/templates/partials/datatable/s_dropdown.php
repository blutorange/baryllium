<?php
    $options = $options ?? [];
    if (empty($options)) {
        return;
    }
    $isI18n = $isI18n ?? true;
?>
<select class="form-control col-search wrapper-search">
    <option value="" selected ><?=$this->egettext('datatable.select.none')?></option>
    <?php foreach ($options as $value => $name): ?>
        <option value="<?=$this->e($value)?>"><?=$isI18n ? $this->egettext($name) : $this->e($name)?></option>
    <?php endforeach; ?>
</select>