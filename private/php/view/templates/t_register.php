<?php $this->layout('portal', ['title' => 'Register']); ?>

<?php $this->insert('partials/dialog', ['id' => 'dialog-agb',
    'title' => 'register.agb.header',
    'body' => $this->fetch('partials/agb')]); ?>

<form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
    <?php if (!empty($registerFormTitle)): ?>
        <h1><?= $this->egettext($registerFormTitle) ?></h1>
    <?php endif; ?>
    
    <?php
    $this->insert('partials/form/input', ['label' => 'register.studentid',
        'name' => 'studentid', 'required' => true,
        'pattern' => '\s*s?[\d]{7}@?.*',
        'patternMessage' => 'register.studentid.pattern',
        'remote' => $this->getResource('public/servlet/checkStudentId.php?studentid={value}'),
        'remoteMessage' => 'register.studentid.exists',
        'placeholder' => 'register.studentid.hint'])
    ?>

    <?php
    $this->insert('partials/form/input', ['label' => 'register.pass.cdual',
        'name' => 'passwordcdual', 'required' => true, 'type' => 'password',
        'minlength' => 5, 'placeholder' => 'register.cdual.hint'])
    ?>

        
    <?php
    $this->insert('partials/form/input', ['label' => 'register.pass',
        'name' => 'password', 'required' => true, 'type' => 'password',
        'minlength' => 5, 'placeholder' => 'register.pass.hint'])
    ?>
    
    <?php
    $this->insert('partials/form/input', ['label' => 'register.pass.repeat',
        'name' => 'password-repeat', 'required' => true, 'type' => 'password',
        'equalto' => '#password', 'equaltoMessage' => 'register.pass.mustequal',
        'placeholder' => 'register.pass.repeat.hint'])
    ?>
    
    <?php 
    $this->insert('partials/form/checkbox', ['label' => 'register.agb',
        'escapeLabel' => false, 'name' => 'agb', 'required' => true])
    ?>
    
    <div class="">
        <button id="password" class="btn btn-primary" name="btnSubmit" type="submit">
            <?= $this->egettext('register.submit') ?>
        </button>
    </div>    
</form>