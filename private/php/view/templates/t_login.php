<?php

use League\Plates\Template\Template;
use Moose\Util\CmnCnst;
use Moose\ViewModel\SectionBasic;
    /* @var $this Template */
    $this->layout('portal', ['title' => 'Login']);
    $this->setActiveSection(SectionBasic::$LOGIN);
?>

<form id="login_form" novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
    <?php
    $this->insert('partials/form/input', ['label' => 'login.studentid',
        'name' => 'studentid',
        'required' => true,
        'pattern' => '\s*(s?[\d]{7}@?.*|sadmin\s*)',
        'patternMessage' => 'login.studentid.pattern',
        'placeholder' => 'login.studentid.hint'])
    ?>

    <?php
    $this->insert('partials/form/input', ['label' => 'register.pass',
        'name' => 'password', 'required' => true, 'type' => 'password',
        'minlength' => 5, 'placeholder' => 'register.pass.hint'])
    ?>
    
    <?php
    $this->insert('partials/form/dropdown', [
        'label' => 'login.language',
        'name' => 'lang',
        'value' => $locale,
        'options' => [
            'de' => 'lang.german',
            'en' => 'lang.english'
        ]        
    ])
            
    ?>
    <?php $this->insert('partials/form/checkbox', [
        'label'            => 'login.remember',
        'name'             => 'rememberLogin',
        'inline'           => false
    ]);
    ?>
    
    <div class="">
        <?=$this->insert('partials/component/tc_action_button', [
            'button' => Moose\ViewModel\ButtonFactory::makeSubmitButton()
                ->setLabelI18n('register.submit')
        ])?>
    </div> 
</form>

<p class="top-space">
    <small>
        <?=$this->egettext('login.not.registered')?>
        <a id="login_register" href="<?=$this->getResource(CmnCnst::PATH_REGISTER)?>">
            <?=$this->egettext('login.goto.register')?>
        </a>
    </small>
</p>

<p>
    <small>
        <?=$this->egettext('login.pw.recovery')?>
        <a id="login_pwrecover" href="<?=$this->getResource(CmnCnst::PATH_PWRECOVERY)?>">
            <?=$this->egettext('login.goto.pwrecovery')?>
        </a>
    </small>
</p>