<?php $this->layout('portal', ['title' => 'Register']) ?>
<?php
$action = $action ?? $selfUrl ?? $_SERVER['PHP_SELF'];
?>

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

<form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action) ?>">
    <?php
    $this->insert('partials/form/input', ['label' => 'register.username',
        'name' => 'username', 'required' => true,
        'pattern' => '([a-z][A-Z]_-)+',
        'remote' => '../../../../public/servlet/CheckUserName.php?username={value}',
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
        'remote' => '../../../../public/servlet/CheckUserMail.php?mail={value}',
        'remoteMessage' => 'register.mail.exists',
        'placeholder' => 'register.mail'])
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
    
    <!-- TODO Add tole -->
    <div class="">
        <button id="password" class="btn btn-primary" name="btnSubmit" type="submit">
            <?= $this->egettext('register.submit') ?>
        </button>
    </div>    
</form>