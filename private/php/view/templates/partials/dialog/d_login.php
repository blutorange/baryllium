<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\BaseButton;
    use Moose\ViewModel\ButtonFactory;
    use Moose\ViewModel\ButtonInterface;
    /* @var $this Template|PlatesMooseExtension */
?>
    <?php
        $this->insert('partials/component/tc_dialog', [
            'id' => 'login_dialog',
            'title' => 'dialog.login.title',
            'mainTag' => 'form',
            'dismissButton' => ButtonFactory::makeLoginCloseDialog(false)
                    ->addHtmlClass('btn-dialog-close')
                    ->addHtmlClass('close')
                    ->setType(ButtonInterface::TYPE_NONE)
                    ->setLabel('×')
                    ->addHtmlAttribute('data-dismiss', 'modal'),
            'buttons' => [
                ButtonFactory::makeLoginDialogButton()
                    ->setLabelI18n('dialog.login.submit')
                    ->build(),
                ButtonFactory::makeLoginCloseDialog()
                    ->addHtmlClass('btn-dialog-close')
                    ->setLabelI18n('dialog.login.close')
                    ->setType(BaseButton::TYPE_INFO)
                    ->build()
            ],
            'body' => [
                '<form class="bootstrap-parsley">',
                '<div class="alert alert-info">',
                $this->gettext('dialog.login.message'),
                '</div>',
                '<div class="dialog-login-failure alert alert-danger hidden">',
                $this->gettext('dialog.login.failed'),
                '</div>',                
                $this->fetch('partials/component/tc_login', [
                    'withLanguageSelector' => false
                ]),
                '</form>'
            ]
        ]);