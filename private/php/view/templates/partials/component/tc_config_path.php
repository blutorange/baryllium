<fieldset>
    <legend><?=$this->egettext('settings.config.heading')?></legend>
    <?php $this->insert('partials/form/input', [
        'name' => 'configpath',
        'placeholder' => '/path/to/config/file.yml',
        'placeholderI18n' => false,
        'required' => true,
        'label' => 'settings.config.path',
        'value' => $form['configpath'] ?? ''
    ])
    ?>
</fieldset>
