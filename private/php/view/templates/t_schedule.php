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

<table class="table table-stripped table-bordered table-hover">
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
        <?php foreach($lessonList as $lesson): ?>
        <tr>
            <td><?=$lesson->getStart()->format('d.m.Y')?></td>
            <td><?=$lesson->getEnd()->format('d.m.Y')?></td>
            <td><?=$lesson->getTitle()?></td>
            <td><?=$lesson->getInstructor()?></td>
            <td><?=$lesson->getRoom()?></td>
        </tr>
        <tr>
            <td colspan="5">Beschreibung: <?=$lesson->getDescription()?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
