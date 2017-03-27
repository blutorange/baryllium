<?php $this->layout('portal', ['title' => 'Login']); ?>

<form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
    <?php
    $this->insert('partials/form/input', ['label' => 'login.studentid',
        'name' => 'studentid', 'required' => true,
        'pattern' => '\s*(s?[\d]{7}@?.*|sadmin\s*)',
        'patternMessage' => 'register.studentid.pattern',
        'placeholder' => 'register.studentid.hint'])
    ?>

    <?php
    $this->insert('partials/form/input', ['label' => 'register.pass',
        'name' => 'password', 'required' => true, 'type' => 'password',
        'minlength' => 5, 'placeholder' => 'register.pass.hint'])
    ?>
    
    <div class="">
        <button id="password" class="btn btn-primary" name="btnSubmit" type="submit">
            <?= $this->egettext('register.submit') ?>
        </button>
    </div>    
</form>