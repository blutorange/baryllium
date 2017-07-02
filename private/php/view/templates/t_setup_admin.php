<?php $this->layout('setup'); ?>

<div class="container">
    <form class="col-md-9" id="setup_admin_form" novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
        <?php if (!empty($formTitle)): ?>
            <h1><?= $this->egettext($formTitle) ?></h1>
        <?php endif; ?>

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

        <div class="">
            <button id="password" class="btn btn-primary" name="btnSubmit" type="submit">
                <?= $this->egettext('register.submit') ?>
            </button>
        </div>    
    </form>
</div>