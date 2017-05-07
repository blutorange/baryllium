<?php
    use League\Plates\Template\Template;
    use Moose\Entity\Lesson;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\SectionBasic;
    /* @var $lessonList Lesson[] */
    /* @var $this Template|PlatesMooseExtension */    
    $this->layout('portal');
    $this->setActiveSection(SectionBasic::$SCHEDULE);
    
    $lessonList = $lessonList ?? [];
?>

<h1><?=$this->egettext('schedule.heading')?></h1>
<div id="schedule_separate" class="schedule">
    <?=$this->egettext('schedule.loading')?>
</div>

<!--table class="table table-stripped table-bordered table-hover">
    <thead>
        <tr>
            <th>Anfang</th>
            <th>Ende</th>
            <th>Name</th>
            <th>Dozent</th>
            <th>Raum</th>
        </tr>
    </thead>
    <tbody>
        <?php /*foreach($lessonList as $lesson):*/ ?>
        <tr>
            <td><?=/*$lesson->getStart()->format('d.m.Y')*/0;?></td>
            <td><?=/*$lesson->getEnd()->format('d.m.Y')*/0;?></td>
            <td><?=/*$lesson->getTitle()*/0;?></td>
            <td><?=/*$lesson->getInstructor()*/0;?></td>
            <td><?=/*$lesson->getRoom()*/0;?></td>
        </tr>
        <tr>
            <td colspan="5">Beschreibung: <?=/*$lesson->getDescription()*/0;?></td>
        </tr>
        <?php /*endforeach;*/ ?>
    </tbody>
</table-->
