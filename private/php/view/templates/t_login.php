<?php
    use League\Plates\Template\Template;
    use Ui\Section;
    /* @var $this Template */
    $this->layout('portal', ['title' => 'Login']);
    $this->setActiveSection(Section::$LOGIN);
?>

<form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
    <?php
    $this->insert('partials/form/input', ['label' => 'login.studentid',
        'name' => 'studentid', 'required' => true,
        'pattern' => '\s*(s?[\d]{7}@?.*|sadmin\s*)',
        'patternMessage' => 'login.studentid.pattern',
        'placeholder' => 'login.studentid.hint'])
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