<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */
    $this->layout('portal', ['title' => 'Mail Settings']);
    $this->setActiveSection(SectionBasic::$SITE_SETTINGS_MAIL);
?>
<div class="container">
    <form id="settings_mail_form"
          class="requires-login"
          data-bootstrap-parsley
          novalidate
          method="POST"
          action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">

        <input type="hidden" name="submitButton"/>
        
        <?php $this->insert('partials/component/tc_config_path', ['form' => $form]) ?>
        
        <fieldset>
            <legend><?=$this->egettext('settings.mail.heading')?></legend>

            <?php $this->insert('partials/form/input', [
                'name' => 'sysmail',
                'placeholder' => 'admin@example.com',
                'placeholderI18n' => false,
                'required' => true,
                'label' => 'setup.system.sysmail',
                'value' => $form['sysmail'] ?? ''
            ])
            ?>

            <?php $this->insert('partials/form/dropdown', [
                'name' => 'mailtype',
                'required' => false,
                'label' => 'setup.system.mailtype',
                'value' => $form['mailtype'] ?? '',
                'options' => [
                    'php' => 'setup.system.mailtype.php',
                    'smtp' => 'setup.system.mailtype.smtp'
                ]
            ])
            ?>

            <fieldset id="smtp">
                <legend>SMTP options</legend>

               <?php $this->insert('partials/form/input', [
                    'name' => 'smtphost',
                    'placeholder' => 'smtp.gmail.com',
                    'placeholderI18n' => false,
                    'required' => true,
                    'label' => 'setup.system.smtphost',
                    'value' => $form['smtphost'] ?? ''
                ])
                ?>

                <?php $this->insert('partials/form/input', [
                    'name' => 'smtpuser',
                    'placeholder' => '...@gmail.com',
                    'placeholderI18n' => false,
                    'required' => true,
                    'label' => 'setup.system.smtpuser',
                    'value' => $form['smtpuser'] ?? ''
                ])
                ?>

                <?php $this->insert('partials/form/input', [
                    'name' => 'smtppass',
                    'placeholder' => '12xy$%',
                    'placeholderI18n' => false,
                    'required' => true,
                    'type' => 'password',
                    'label' => 'setup.system.smtppass',
                    'value' => $form['smtppass'] ?? ''
                    ])
                ?>

                <?php $this->insert('partials/form/input', [
                    'name' => 'smtpport',
                    'placeholder' => '465',
                    'placeholderI18n' => false,
                    'required' => true,
                    'type' => 'number',
                    'min' => 1,
                    'max' => 0xFFFF-1,
                    'label' => 'setup.system.smtpport',
                    'value' => $form['smtpport'] ?? '465'
                    ])
                ?>

                <?php $this->insert('partials/form/dropdown', [
                    'name' => 'smtpsec',
                    'required' => false,
                    'label' => 'setup.system.smtpsec',
                    'value' => $form['smtpsec'] ?? '',
                    'options' => [
                        'ssl' => 'setup.system.smtpsec.ssl',
                        'tls' => 'setup.system.smtpsec.tls',
                    ]
                ])
                ?>

                <?php $this->insert('partials/form/input', [
                    'name' => 'smtptime',
                    'placeholder' => '20',
                    'placeholderI18n' => false,
                    'required' => false,
                    'type' => 'number',
                    'min' => 0,
                    'max' => 9999,
                    'label' => 'setup.system.smtptime',
                    'value' => $form['smtptime'] ?? '20'
                    ])
                ?>

                <?php $this->insert('partials/form/input', [
                    'name' => 'smtpbind',
                    'placeholder' => '0',
                    'placeholderI18n' => false,
                    'label' => 'setup.system.smtpbind',
                    'value' => $form['smtpbind'] ?? '0'
                ])
                ?>

                <?php $this->insert('partials/form/checkbox', [
                    'name' => 'smtppers',
                    'label' => 'setup.system.smtppers',
                    'value' => ($form['smtppers'] ?? '') === 'on'
                ])
                ?>
            </fieldset>
        </fieldset>
        
        <fieldset>
            <legend><?=$this->egettext('settings.testmail.heading')?></legend>
            <?php $this->insert('partials/form/input', [
                'name' => 'testmail',
                'placeholder' => 'admin@example.com',
                'placeholderI18n' => false,
                'required' => true,
                'label' => 'settings.testmail.address',
                'value' => $form['testmail'] ?? ''
            ])
            ?>
        </fieldset>
        <div class="button-group">
            <button data-action="test" id="tst_btn" class="submit-button btn btn-default btn-block" name="btnTest" type="submit">
                <?= $this->egettext('settings.mail.test') ?>
            </button>
            <button data-action="save" id="sbm_btn" class="submit-button btn btn-default btn-block" name="btnSubmit" type="submit">
                <?= $this->egettext('settings.mail.submit') ?>
            </button>
        </div>        
    </form>    
</div>

<script type="text/javascript">
    (function($) {
        $(function() {
            var $element = $('#mailtype');
            var onChange = function() {
                if ($('option:selected', $element).val() === 'php') {
                    $('#smtp').hide().find('[data-required]').removeAttr('required');
                }
                else {
                    $('#smtp').show().find('[data-required]').attr('required', '1');
                }
            }
            onChange();
            $element.on("change", onChange);
        });
    })(window.Moose.Library.jQuery);
</script>