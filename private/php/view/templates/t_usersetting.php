<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\SectionBasic;
    use Moose\ViewModel\ButtonFactory;

    /* @var $this Template|PlatesMooseExtension */
    $this->layout('portal', ['title' => 'User setting']);
    $this->setActiveSection(SectionBasic::$USER_SETTING);
    $forUser = $forUser ?? $this->getUser();    
    $avatar = $user->getAvatar();
?>

<div class="container">
    
    <div class="profile-avatar-area">
        <div class="avatar-img-wrapper">
            <h2><?= $this->egettext('settings.nav')?></h2>
            <?php if ($avatar !== null): ?>
                <img alt="User profile image." class="avatar-img center-block" src="<?= $this->e($avatar)?>"/>
            <?php else: ?>
                <p class="center-block text-center">No avatar set</p>
            <?php endif; ?>
        </div> 
    </div>
    
    <div id="settings" class="moose-white">
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
                $this->insert('partials/form/dropdown', [
                    'label'            => 'option.pref.dhall.label',
                    'name'             => 'preferredDiningHall',
                    'optionI18n'       => false,
                    'persist'          => 'server',
                    'persistNamespace' => 'userOptionServlet',
                    'persistUid'       => $forUser->getId(),                    
                    'options'          => $diningHalls ?? []
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
</div>