<?php $this->layout('portal', ['title' => 'Register']); ?>

<div id="dialog-agb" class="modal fade" role="dialog" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" class="close">&times;</button>
                <h4 class="modal-title"><?=$this->egettext('register.agb.header')?></h4>
            </div>
            <div class="modal-body">
                <?php $this->insert('partials/agb'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?=$this->gettext('button.dialog.close')?>
                </button>
            </div>
        </div>
    </div>
</div>

<form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
    <?php if (!empty($registerFormTitle)): ?>
        <h1><?= $this->egettext($registerFormTitle) ?></h1>
    <?php endif; ?>
    
    <?php
    $this->insert('partials/form/dropdown', ['label' => 'register.role',
        'name' => 'role', 'required' => true,
        'options' => ['student' => 'register.role.student', 'lecturer' => 'register.role.lecturer']])
    ?>

    <?php
    $this->insert('partials/form/input', ['label' => 'register.username',
        'name' => 'username', 'required' => true,
        'pattern' => '[0-9a-zA-Z_-]+',
        'patternMessage' => 'register.username.pattern',
        'remote' => $this->getResource('public/servlet/CheckUserName.php?username={value}'),
        'remoteMessage' => 'register.username.exists',
        'placeholder' => 'register.username.hint'])
    ?>

    <?php
    $this->insert('partials/form/input', ['label' => 'register.firstname',
        'name' => 'firstname', 'required' => false,
        'placeholder' => 'register.firstname.hint'])
    ?>

    <?php
    $this->insert('partials/form/input', ['label' => 'register.lastname',
        'name' => 'lastname', 'required' => false,
        'placeholder' => 'register.lastname.hint'])
    ?>

    <?php
    $this->insert('partials/form/input', ['label' => 'register.mail',
        'name' => 'mail', 'required' => true,
        'type' => 'email',
        'remote' => $this->getResource('public/servlet/CheckUserMail.php?mail={value}'),
        'remoteMessage' => 'register.mail.exists',
        'placeholder' => 'register.mail.hint'])
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