<?php
    use League\Plates\Template\Template;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template */
    $this->layout('portal', ['title' => 'Reset password']);
    $this->setActiveSection(SectionBasic::$PW_RESET);
?>

<form id="pwreset-form" novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
    <?php if (!empty($registerFormTitle)): ?>
        <h1><?= $this->egettext($registerFormTitle) ?></h1>
    <?php endif; ?>

    <?php
    $this->insert('partials/form/input', ['label' => 'pwreset.pass',
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
        <button id="sbm_btn" class="btn btn-primary" name="btnSubmit" type="submit">
            <?= $this->egettext('register.submit') ?>
        </button>
    </div>    
</form>