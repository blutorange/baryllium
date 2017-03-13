<?php $this->layout('portal', ['title' => 'Register']) ?>
<?php
    $action = $action ?? $selfUrl ?? $_SERVER['PHP_SELF'];
?>

<form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action) ?>">
    <?php $this->insert('partials/form/input', ['label' => $this->gettext('register.user'),
        'name' => 'username', 'required' => true,
        'remote' => '../../../../public/servlet/CheckUsername.php?username={value}',
        'remoteMessage' => $this->gettext('register.user.exists'),
        'placeholder' => $this->gettext('register.user.hint')]) ?>

    <?php $this->insert('partials/form/input', ['label' => $this->gettext('register.pass'),
        'name' => 'password', 'required' => true, 'mask' => true,
        'minlength' => 5, 'placeholder' => $this->gettext('register.pass.hint')]) ?>
    
    <?php $this->insert('partials/form/input', ['label' => $this->gettext('register.mail'),
        'name' => 'mail', 'required' => true,
        'placeholder' => $this->gettext('register.mail.hint')]) ?>
    
    <?php $this->insert('partials/form/input', ['label' => $this->gettext('register.role'),
        'name' => 'role', 'required' => true,
        'placeholder' => $this->gettext('register.role.hint')]) ?>

    <div class="">
        <button id="password" class="btn btn-primary" name="btnSubmit" type="submit"><?= $this->e($this->gettext('register.submit')) ?></button>
    </div>    
</form>