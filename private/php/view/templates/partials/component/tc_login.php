<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    /* @var $this Template|PlatesMooseExtension */  
    $idSuffix = $idSuffix ?? '';
?>
    <?php
    $this->insert('partials/form/input', [
        'label' => 'login.studentid',
        'id'             => "studentid$idSuffix",        
        'name'           => 'studentid',
        'required'       => true,
        'pattern'        => '\s*(s?[\d]{7}@?.*|sadmin\s*)',
        'patternMessage' => 'login.studentid.pattern',
        'placeholder'    => 'login.studentid.hint',
        'value'          => $this->getUser()->getStudentId()])
    ?>

    <?php
    $this->insert('partials/form/input', ['label' => 'register.pass',
        'id'          => "password$idSuffix",
        'name'        => 'password',
        'required'    => true,
        'type'        => 'password',
        'minlength'   => 5,
        'placeholder' => 'register.pass.hint'])
    ?>

    <?php if ($withLanguageSelector ?? false) {
        $this->insert('partials/form/dropdown', [
            'id'     => "login.language$idSuffix",
            'label' => 'login.language',
            'name' => 'lang',
            'value' => $locale,
            'options' => [
                'de' => 'lang.german',
                'en' => 'lang.english'
            ]        
        ]);
    }?>

    <?php $this->insert('partials/form/checkbox', [
        'id'     => "login.remember$idSuffix",
        'label'  => 'login.remember',
        'name'   => 'rememberLogin',
        'inline' => false,
        'value'  => $this->getUser()->isCookieAuthed()
    ]);
    ?>