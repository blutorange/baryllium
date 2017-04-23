<?php
    use League\Plates\Template\Template;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template */
    $this->layout('portal', ['title' => 'Forgot password']);
    $this->setActiveSection(SectionBasic::$PW_RECOVERY);
?>

<form id="pwrecover_form" novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">   
    <?php
    $this->insert('partials/form/input', ['label' => 'register.studentid',
        'name' => 'studentid', 'required' => true,
        'pattern' => '\s*s?[\d]{7}@?.*',
        'patternMessage' => 'register.studentid.pattern',
        'remote' => $this->getResource('public/servlet/checkStudentIdExists.php?studentid={value}'),
        'remoteMessage' => 'pwrecover.studentid.notexists',
        'placeholder' => 'register.studentid.hint'])
    ?>
    
    <div class="">
        <button id="sbm_btn" class="btn btn-primary" name="btnSubmit" type="submit">
            <?= $this->egettext('pwrecover.submit') ?>
        </button>
    </div>    
</form>