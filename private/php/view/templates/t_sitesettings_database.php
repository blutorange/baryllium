<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */
    $this->layout('portal', ['title' => 'Database Settings']);
    $this->setActiveSection(SectionBasic::$SITE_SETTINGS_DATABASE);
?>
<div class="container">
    <form id="settings_database_form"
      class="requires-login"
      data-bootstrap-parsley
      novalidate
      method="POST"
      action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
        
        <input type="hidden" name="submitButton"/>                
    
        <?php $this->insert('partials/component/tc_config_path', ['form' => $form]) ?>

        <fieldset>
            <legend><?=$this->egettext('settings.database.heading')?></legend>
            <?php $this->insert('partials/form/input', [
                'name' => 'host',
                'placeholder' => '127.0.0.1',
                'placeholderI18n' => false,
                'required' => true,
                'label' => 'setup.system.host',
                'value' => $form['host'] ?? '127.0.0.1'
            ])
            ?>

            <?php $this->insert('partials/form/input', [
                'name' => 'port',
                'type' => 'number',
                'placeholder' => '3306',
                'placeholderI18n' => false,
                'required' => true,
                'label' => 'setup.system.port',
                'min' => 1,
                'max' => 65535,
                'value' => $form['port'] ?? ''
            ])
            ?>

            <?php $this->insert('partials/form/dropdown', [
                'name' => 'driver',
                'required' => true,
                'label' => 'setup.system.driver',
                'value' => $form['driver'] ?? '',
                'options' => [
                    'mysql' => 'setup.system.driver.mysql',
                    'oracle' => 'setup.system.driver.oracle',
                    'sqlite' => 'setup.system.driver.sqlite',
                    'sqlserver' => 'setup.system.driver.sqlserver',
                    'postgres' => 'setup.system.driver.postgres'
                ]
            ])
            ?>

            <?php $this->insert('partials/form/input', [
                'name' => 'dbname',
                'placeholder' => 'baryllium',
                'placeholderI18n' => false,
                'required' => true,
                'label' => 'settings.db.dbname',
                'value' => $form['dbname'] ?? 'baryllium'
            ])
            ?>

            <?php $this->insert('partials/form/input', [
                'name' => 'user',
                'required' => true,
                'placeholder' => 'baryllium',
                'placeholderI18n' => false,
                'label' => 'setup.system.user',
                'value' => $form['user'] ?? 'baryllium'
            ])
            ?>

            <?php $this->insert('partials/form/input', [
                'name' => 'pass',
                'type' => 'password',
                'required' => true,
                'label' => 'setup.system.pass',
                'value' => $form['pass'] ?? ''
            ])
            ?>

            <?php $this->insert('partials/form/input', [
                'name' => 'collation',
                'type' => 'text',
                'placeholder' => 'utf8_general_ci',
                'placeholderI18n' => false,
                'required' => true,
                'label' => 'setup.system.collation',
                'value' => $form['collation'] ?? 'utf8_general_ci'
            ])
            ?>

            <?php $this->insert('partials/form/input', [
                'name' => 'encoding',
                'type' => 'text',
                'placeholder' => 'utf8',
                'placeholderI18n' => false,
                'required' => true,
                'label' => 'setup.system.encoding',
                'value' => $form['encoding'] ?? 'utf8'
            ])
            ?>   
        </fieldset>
        
        <div class="button-group">
            <button data-action="test" id="tst_btn" class="submit-button btn btn-default btn-block" name="btnTest" type="submit">
                <?= $this->egettext('settings.db.test') ?>
            </button>
            <button data-action="save" id="sbm_btn" class="submit-button btn btn-default btn-block" name="btnSubmit" type="submit">
                <?= $this->egettext('settings.db.save') ?>
            </button>
        </div>       
        
    </form>
</div>