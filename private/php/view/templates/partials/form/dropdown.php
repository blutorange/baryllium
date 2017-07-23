<?php 
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    /* @var $this Template|PlatesMooseExtension */
    $name = $name ?? 'select';
    $id = $id ?? $name;
    $required = $required ?? false;    
    $label = $label ?? 'label';
    $remote = $remote ?? '';
    $remoteMessage = $remoteMessage ?? '';
    $equalto = $equalto ?? '';
    $equaltoMessage = $equaltoMessage ?? '';
    $value = $value ?? null;
    $persist = $persist ?? '';
    $options = $options ?? [];
    $styleClassesSelect = '';
    $optionI18n = $optionI18n ?? true;
    $select2 = $select2 ?? null;
    if (!empty($select2)) {
        $styleClassesSelect .= ' select2 ';
    }
    if (!empty($persist)) {
        $styleClassesSelect .= ' persist ';
    }
?>
<div class="form-group">
    <label class="control-label" for="<?=$this->e($id)?>">
        <?= $this->e($this->egettext($label)) ?>
        <?php if ($required): ?><span class="required-star"> *</span><?php endif; ?>
    </label>
    <select
        class="form-control bootstrap-select <?=$styleClassesSelect?>"
        id="<?=$this->e($id)?>"
        name="<?=$this->e($name)?>"
        <?php if (!empty($persist)): ?>
            data-persist-type="<?=$persist?>"
            data-persist-namespace="<?=$this->e($persistNamespace ?? 'fields')?>"
            data-persist-uid="<?=$this->e($persistUid ?? 0)?>"
        <?php endif;?>
        data-c-select2="<?=$this->e(json_encode($select2))?>"            
        <?php if (!empty($equalto)): ?> data-parsley-equalto="<?=$this->e($equalto)?>" <?php endif; ?>
        <?php if (!empty($equaltoMessage)): ?> data-parsley-equalto-message="<?=$this->egettext($equaltoMessage)?>" <?php endif; ?>
        <?php if (!empty($remote)): ?> data-parsley-remote="<?= $this->e($remote)?>" <?php endif; ?>
        <?php if (!empty($remoteMessage)): ?> data-parsley-remote-message="<?= $this->egettext($remoteMessage)?>" <?php endif; ?>        
        <?php if ($required): ?> required <?php endif; ?>
    >
        <?php if ($required): ?> <option value=""><?=$this->egettext('form.select.required.option')?></option><?php endif; ?>
        <?php foreach ($options as $optionValue => $optionText) { ?>
            <option value="<?=$this->e($optionValue)?>" <?= $value === $optionValue ? 'selected' : ''?>>
                <?=$optionI18n ? $this->egettext($optionText) : $this->e($optionText) ?>
            </option>
        <?php } ?>
    </select>
</div>