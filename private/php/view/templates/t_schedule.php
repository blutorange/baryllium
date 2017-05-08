<?php
    use League\Plates\Template\Template;
    use Moose\Entity\Lesson;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */    
    $this->layout('portal');
    $this->setActiveSection(SectionBasic::$SCHEDULE);
?>

<h1><?=$this->egettext('schedule.heading')?></h1>
<div id="schedule_separate" class="schedule schedule-auto">
    <?=$this->egettext('schedule.loading')?>
</div>