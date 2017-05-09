<?php

use League\Plates\Template\Template;
use Moose\PlatesExtension\PlatesMooseExtension;
use Moose\Util\PermissionsUtil;
use Moose\ViewModel\ButtonFactory;
use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */    
    $this->layout('portal');
    $this->setActiveSection(SectionBasic::$SCHEDULE);
?>

<h1><?=$this->egettext('schedule.heading')?></h1>

<div id="schedule_separate" class="schedule schedule-auto">
    <?=$this->egettext('schedule.loading')?>
</div>

<?php if (PermissionsUtil::assertCampusDualForUser(null, false)): ?>
    <?= $this->insert('partials/component/tc_action_button', [
        'button' => ButtonFactory::makeUpdateSchedule()
            ->setLabelI18n('button.schedule.refresh')
            ->addHtmlClass('btn-block')
            ->addCallbackOnClickData('msgConfirm', $this->gettext('confirm.schedule.refresh'))
            ->addCallbackOnClickData('selector', '#schedule_separate')
    ])?>
<?php endif; ?>