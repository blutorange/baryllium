<?php $this->layout('portal', ['title' => 'Register']) ?>
<?php
$action = $action ?? $selfUrl ?? $_SERVER['PHP_SELF'];
?>

<form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action) ?>">
    <?php
    $this->insert('partials/form/input', ['label' => $this->gettext('register.username'),
        'name' => 'username', 'required' => true,
        'placeholder' => $this->gettext('register.username.hint')])
    ?>

    <?php
    $this->insert('partials/form/input', ['label' => $this->gettext('register.firstname'),
        'name' => 'firstname', 'required' => false,
        'placeholder' => $this->gettext('register.firstname.hint')])
    ?>

    <?php
    $this->insert('partials/form/input', ['label' => $this->gettext('register.lastname'),
        'name' => 'lastname', 'required' => false,
        'placeholder' => $this->gettext('register.lastname.hint')])
    ?>

    <?php
    $this->insert('partials/form/input', ['label' => $this->gettext('register.mail'),
        'name' => 'mail', 'required' => true,
        'remote' => '../../../../public/servlet/CheckUserMail.php?mail={value}',
        'remoteMessage' => $this->gettext('register.mail.exists'),
        'placeholder' => $this->gettext('register.mail')])
    ?>

    <?php
    $this->insert('partials/form/input', ['label' => $this->gettext('register.pass'),
        'name' => 'password', 'required' => true, 'mask' => true,
        'minlength' => 5, 'placeholder' => $this->gettext('register.pass.hint')])
    ?>

    <?php 
    $this->insert('partials/form/checkbox', ['label' => $this->gettext('register.agb'),
        'name' => 'agb', 'required' => true, 'mask' => true, 'placeholder' => $this->gettext('register.agb.hint')])
    ?>


    <div class="">
        <button id="password" class="btn btn-primary" name="btnSubmit" type="submit"><?= $this->e($this->gettext('register.submit')) ?></button>
    </div>    
</form>