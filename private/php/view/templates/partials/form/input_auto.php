<?php 
    $name = $model->getName() ?? 'input';
    $id = $model->getId() ?? $name;
    $value = $model->getForForm() ?? '';

    $placeholder = $view->getIsPlaceholder() ? $model->getPlaceholder() ?? '' : '';
    $label = $model->getLabel() ?? $name;

    $remote = '';
    $remoteMessage = '';
    
    $required = array_key_exists(\Symfony\Component\Validator\Constraints\NotBlank::class, $constraints) || array_key_exists(Symfony\Component\Validator\Constraints\NotNull::class, $constraints);
    
    if (array_key_exists(\Symfony\Component\Validator\Constraints\Length::class, $constraints)) {
        $lengthSym = $constraints[\Symfony\Component\Validator\Constraints\Length::class];
        $minlength = $lengthSym->min;
        $maxlength = $lengthSym->max;
    }
    else {
        $minlength = 0;
        $maxlength = 0;
    }
    
    if ($view->getIsMasked()) {
        $type = 'password';
    }
    else if (array_key_exists(Symfony\Component\Validator\Constraints\Email::class, $constraints)) {
        $type = 'email';
    }
    else if (array_key_exists(Symfony\Component\Validator\Constraints\Url::class, $constraints)) {
        $type = 'url';
    }
    else {
        $type = 'text';
    }

    if (array_key_exists(\Symfony\Component\Validator\Constraints\Regex::class, $constraints)) {
        $regex = $constraints[\Symfony\Component\Validator\Constraints\Regex::class];
        if ($regex->htmlPattern === null) {
            $pattern = $regex->pattern;
            $pattern = substr($pattern, 1, strlen($pattern)-2);
            
        }
        else {
            $pattern = $regex->htmlPattern;
        }
        $patternMessage = $regex->message;
    }
    else {
        $pattern = '';
        $patternMessage = '';
    }

    if (array_key_exists(\Symfony\Component\Validator\Constraints\EqualTo::class, $constraints)) {
        $equalToSym = $constraints[\Symfony\Component\Validator\Constraints\EqualTo::class] -> value;
        $equalto = $equalToSym-> value;
        $equaltoMessage = $equalToSym->message;
    }
    else {
        $equalto = $equalto ?? '';
        $equaltoMessage = $equaltoMessage ?? '';
    }
?>
<div class="form-group">
    <label class="control-label" for="<?=$this->e($id)?>">
        <?= $this->e($this->egettext($label)) ?>
        <?php if ($required): ?><span class="required-star"> *</span><?php endif; ?>
    </label>
    <input
        class="form-control"
        id="<?=$this->e($id)?>"
        name="<?=$this->e($name)?>"
        type="<?=$this->e($type)?>"
        value="<?=$this->e($value)?>"
        <?php if (!empty($equalto)): ?> data-parsley-equalto="<?=$this->e($equalto)?>" <?php endif; ?>
        <?php if (!empty($equaltoMessage)): ?> data-parsley-equalto-message="<?=$this->egettext($equaltoMessage)?>" <?php endif; ?>
        <?php if (!empty($pattern)): ?> pattern="<?=$this->e($pattern)?>"<?php endif; ?>
        <?php if (!empty($patternMessage)): ?> data-parsley-pattern-message="<?=$this->egettext($patternMessage)?>" <?php endif; ?>
        <?php if (!empty($placeholder)): ?> placeholder="<?= $this->gettext($placeholder)?>" <?php endif; ?>
        <?php if (!empty($remote)): ?> data-parsley-remote="<?= $this->e($remote)?>" <?php endif; ?>
        <?php if (!empty($remoteMessage)): ?> data-parsley-remote-message="<?= $this->egettext($remoteMessage)?>" <?php endif; ?>        
        <?php if ($required): ?> required <?php endif; ?>
        <?php if ($minlength > 0): ?> minlength="<?=$this->e($minlength)?>" <?php endif; ?>
        <?php if ($maxlength > 0): ?> maxlength="<?=$this->e($maxlength)?>" <?php endif; ?>
    />
</div>