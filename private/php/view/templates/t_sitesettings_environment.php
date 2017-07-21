<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */
    $this->layout('portal', ['title' => 'Environment Settings']);
    $this->setActiveSection(SectionBasic::$SITE_SETTINGS_ENVIRONMENT);
?>
<div class="container">
    <form id="settings_environment_form"
      class="requires-login"
      data-bootstrap-parsley
      novalidate
      method="POST"
      action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
        <input type="hidden" name="submitButton"/>                

        <?php $this->insert('partials/component/tc_config_path', ['form' => $form]) ?>
        
        <fieldset>
            <legend><?=$this->egettext('settings.security.heading')?></legend>
        
            <?php $this->insert('partials/form/dropdown', [
                    'name' => 'samesite',
                    'required' => false,
                    'label' => 'settings.security.samesite',
                    'value' => $form['samesite'] ?? 'strict',
                    'options' => [
                        'lax' => 'settings.security.samesite.lax',
                        'strict' => 'settings.security.samesite.strict'
                    ]
                ])
            ?>
            
            <?php $this->insert('partials/form/input', [
                'name' => 'remembertime',
                'placeholder' => '86400',
                'placeholderI18n' => false,
                'type' => 'number',
                'min' => 0,
                'required' => true,
                'label' => 'settings.security.remembertime',
                'value' => $form['remembertime'] ?? '86400'
            ])
            ?>    
            
            <?php $this->insert('partials/form/checkbox', [
                'name' => 'httponly',
                'label' => 'settings.security.httponly',
                'value' => \in_array($form['httponly'] ?? true, [true,'on']),
                'inline' => false
            ])
            ?>
            
            <?php $this->insert('partials/form/checkbox', [
                'name' => 'httpsonly',
                'label' => 'settings.security.httpsonly',
                'value' => \in_array($form['httpsonly'] ?? true, [true,'on']),
                'inline' => false
            ])
            ?>
        </fieldset>
        
        <fieldset>
            <legend><?=$this->egettext('settings.paths.heading')?></legend>
        
            <?php $this->insert('partials/form/input', [
                'name' => 'docproxy',
                'placeholder' => '/path/to/doctrine/proxy/directory',
                'placeholderI18n' => false,
                'required' => true,
                'label' => 'settings.paths.docproxy',
                'value' => $form['docproxy'] ?? ''
            ])
            ?>    

            <?php $this->insert('partials/form/input', [
                'name' => 'serverpublic',
                'placeholder' => 'http://mypage.org:8080',
                'placeholderI18n' => false,
                'required' => true,
                'label' => 'settings.paths.serverpublic',
                'value' => $form['serverpublic'] ?? ''
            ])
            ?>    

            <?php $this->insert('partials/form/input', [
                'name' => 'serverlocal',
                'placeholder' => 'http://localhost:82',
                'placeholderI18n' => false,
                'required' => true,
                'label' => 'settings.paths.serverlocal',
                'value' => $form['serverlocal'] ?? ''
            ])
            ?>

            <?php $this->insert('partials/form/input', [
                'name' => 'logfile',
                'placeholder' => '/path/to/logfile.log',
                'placeholderI18n' => false,
                'required' => true,
                'label' => 'settings.paths.logfile',
                'value' => $form['logfile'] ?? ''
            ])
            ?>
        </fieldset>

        <fieldset>
            <legend><?=$this->egettext('settings.logging.heading')?></legend>
            <?php $this->insert('partials/form/dropdown', [
                    'name' => 'loglevel',
                    'required' => false,
                    'label' => 'settings.logging.loglevel',
                    'value' => $form['loglevel'] ?? 'warning',
                    'options' => [
                        'ALL' => 'settings.logging.loglevel.all',
                        'DEBUG' => 'settings.logging.loglevel.debug',
                        'INFO' => 'settings.logging.loglevel.info',
                        'WARNING' => 'settings.logging.loglevel.warning',
                        'ERROR' => 'settings.logging.loglevel.error',
                        'NONE' => 'settings.logging.loglevel.none'
                    ]
                ])
            ?>
        </fieldset>
        
        <div>
            <button data-action="save" id="sbm_btn" class="submit-button btn btn-default btn-block" name="btnSubmit" type="submit">
                <?= $this->egettext('settings.mail.submit') ?>
            </button>
        </div>
        
        <fieldset>
            <legend><?=$this->egettext('settings.cache.heading')?></legend>
            <p><?=$this->egettext('settings.cache.note')?></p>
            <div>
                <button data-action="clear" id="clr_btn" class="submit-button btn btn-default btn-block" name="btnClear" type="submit">
                    <?= $this->egettext('settings.cache.clear') ?>
                </button>            
            </div>
        </fieldset>
    </form>
</div>