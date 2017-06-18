<?php
    use Moose\ViewModel\ButtonFactory;
    $forUser = $forUser ?? $this->getUser();
?>
<div class="moose-white">
    <fieldset>
        <legend><?=$this->egettext('settings.fieldset.publicview')?></legend>
            <?php
                $this->insert('partials/form/checkbox', [
                    'label'            => 'option.vp.studentid.label',
                    'name'             => 'isPublicStudentId',
                    'persist'          => 'server',
                    'persistNamespace' => 'userOptionServlet',
                    'persistUid'       => $forUser->getId(),
                    'inline'           => false
                ]);
                $this->insert('partials/form/checkbox', [
                    'label'            => 'option.vp.firstname.label',
                    'name'             => 'isPublicFirstName',
                    'persist'          => 'server',
                    'persistNamespace' => 'userOptionServlet',
                    'persistUid'       => $forUser->getId(),
                    'inline'           => false
                ]);
                $this->insert('partials/form/checkbox', [
                    'label'            => 'option.vp.lastname.label',
                    'name'             => 'isPublicLastName',
                    'persist'          => 'server',
                    'persistNamespace' => 'userOptionServlet',
                    'persistUid'       => $forUser->getId(),
                    'inline'           => false
                ]);                
                $this->insert('partials/form/checkbox', [
                    'label'            => 'option.vp.tutgroup.label',
                    'name'             => 'isPublicTutorialGroup',
                    'persist'          => 'server',
                    'persistNamespace' => 'userOptionServlet',
                    'persistUid'       => $forUser->getId(),
                    'inline'           => false
                ]);
                $this->insert('partials/form/checkbox', [
                    'label'            => 'option.vp.mail.label',
                    'name'             => 'isPublicMail',
                    'persist'          => 'server',
                    'persistNamespace' => 'userOptionServlet',
                    'persistUid'       => $forUser->getId(),
                    'inline'           => false
                ]);       
            ?>
    </fieldset>
    
    <fieldset>
        <legend><?=$this->egettext('settings.fieldset.ui')?></legend>
        <?php
            $this->insert('partials/form/checkbox', [
                'label'         => 'option.paging.list.label',
                'name'          => 'option.paging.list',
                'persist'       => 'client',
                'inline'        => false
            ]);
            $this->insert('partials/form/checkbox', [
                'label'         => 'option.dashboard.static.label',
                'name'          => 'option.dashboard.static',
                'persist'       => 'cookie',
                'inline'        => false
            ]);
            $this->insert('partials/form/checkbox', [
                'label'         => 'option.documents.treestore.label',
                'name'          => 'option.documents.treestore',
                'persist'       => 'client',
                'inline'        => false
            ]);
            $this->insert('partials/form/input', [
                'label'         => 'option.post.count.label',
                'name'          => 'option.post.count',
                'persist'       => 'cookie',
                'type'          => 'number',
                'min'           => 10,
                'max'           => 40
            ]);
            $this->insert('partials/form/dropdown', [
                'label'         => 'option.edit.mode.label',
                'name'          => 'option.edit.mode',
                'persist'       => 'client',
                'options'       => [
                    'popup' => 'option.edit.mode.popup',
                    'inline' => 'option.edit.mode.inline'
                ]
            ]);    
        ?>
    </fieldset>
    
    <fieldset>
        <legend><?=$this->egettext('settings.fieldset.cdual')?></legend>
        <form novalidate data-bootstrap-parsley class="no-enter">
            <?php $this->insert('partials/form/input', [
                'id'            => 'user_change_pwcd',
                'label'         => 'user.change.pwcd',
                'name'          => 'pwcd',
                'type'          => 'password',
                'required'      => true,
                'minlength'     => 5
            ])?>
            <div class="form-group">
                <?=$this->insert('partials/component/tc_action_button', [
                            'button' => ButtonFactory::makeUpdatePwcd()
                                ->setLabelI18n('user.change.pwcd.submit')
                                ->addCallbackOnClickData('selector', '#user_change_pwcd')
                                ->addCallbackOnClickData('msgSuccess', $this->egettext('user.change.pwcd.success'))
                                ->addCallbackOnClickData('userId', $forUser->getId())
                                ->addHtmlClass('btn-block')
                ])?>
            </div>
        </form>
        <?=$this->insert('partials/component/tc_action_button', [
                        'button' => ButtonFactory::makeRemovePwcd()
                            ->setLabelI18n('user.remove.pwcd.submit')
                            ->addCallbackOnClickData('userId', $forUser->getId())
                            ->addCallbackOnClickData('msgConfirm', $this->egettext('user.remove.pwcd.confirm'))
                            ->addCallbackOnClickData('msgSuccess', $this->egettext('user.remove.pwcd.success'))
                            ->addHtmlClass('btn-block')
        ])?>
    </fieldset>
</div>
