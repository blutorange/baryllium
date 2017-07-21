<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\SectionBasic;
    use Moose\ViewModel\TasksDiningHallModel;
    /* @var $this Template|PlatesMooseExtension */
    /* @var $hall TasksDiningHallModel */
    $this->layout('portal', ['title' => 'Tasks Settings']);
    $this->setActiveSection(SectionBasic::$SITE_SETTINGS_TASKS);    
?>
<div class="container">
    <form id="settings_tasks_dininghall_form"
      class="requires-login"
      data-bootstrap-parsley
      novalidate
      method="POST"
      action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
        
        <input type="hidden" name="submitButton"/>             
        <input type="hidden" name="submitButtonData"/>
    
        <?php $this->insert('partials/component/tc_config_path', ['form' => $form]) ?>

        <fieldset id="tasks_dining_hall_fieldset">
            <legend><?=$this->egettext('settings.tasks.dininghall.heading')?></legend>
            <ul class="list-unstyled">
                <?php foreach ($form['dhall'] as $hall): ?>
                <li class="container">
                    <div class="col-md-3">
                        <?php $this->insert('partials/form/checkbox', [
                            'label'  => $hall->getLocalizedName(),
                            'labelI18n' => false,
                            'name'   => 'dhall[' . $hall->getClass() . '][activated]',
                            'inline' => true,
                            'value'  => $hall->getIsActivated()
                        ]);
                        ?>
                    </div>
                    <div class="col-md-7">
                        <?php $this->insert('partials/form/input', [
                            'name'   => 'dhall[' . $hall->getClass() . '][schedule]',
                            'placeholder' => 'settings.tasks.dhall.schedule.ph',
                            'placeholderI18n' => true,
                            'required' => true,
                            'type' => "number",
                            'min' => 1,
                            'inline' => true,
                            'label' => 'settings.tasks.dhall.schedule',
                            'value' => $hall->getSchedule()
                        ])
                        ?>
                    </div>
                    <div class="col-md-2">
                        <button data-action="testDiningHall"
                                data-action-data="<?=$hall->getClass()?>"
                                class="btn-message-only submit-button btn btn-default btn-sm btn-block"
                                type="submit">
                            <?= $this->egettext('settings.tasks.dininghall.test') ?>
                        </button>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="button-group">
                <button data-action="saveDiningHall" id="sbm_btn_dhall" class="submit-button btn btn-default btn-block" type="submit">
                    <?= $this->egettext('settings.tasks.dininghall.save') ?>
                </button>
            </div>
        </fieldset>
        
        <fieldset>
            <legend><?=$this->egettext('settings.tasks.opal.heading')?></legend>
        </fieldset>
        
        <fieldset>
            <legend><?=$this->egettext('settings.tasks.campusdual.heading')?></legend>
        </fieldset>
        
        <fieldset>
            <legend><?=$this->egettext('settings.tasks.mails.heading')?></legend>
        </fieldset>
        
        <fieldset>
            <legend><?=$this->egettext('settings.tasks.cleanup.heading')?></legend>
        </fieldset>

    </form>
</div>