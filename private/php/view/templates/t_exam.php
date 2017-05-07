<?php
    use League\Plates\Template\Template;
    use Moose\Entity\Exam;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\SectionBasic;
    /* @var $examList Exam[] */
    /* @var $this Template|PlatesMooseExtension */    
    $this->layout('portal');
    $this->setActiveSection(SectionBasic::$EXAM);

    $examList = $examList ?? [];    
?>

<table class="table table-stripped table-bordered table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Vorlesung</th>
            <th>Bewertung</th>
            <th>Datum der Bewertung</th>
            <th>Datum der Ankündigung</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($examList as $exam): ?>
        <tr>
            <td><?=$exam->getExamId()?></td>
            <td><?=$exam->getTitle()?></td>
            <td><?=$exam->getMarkString()?></td>
            <td><?=$exam->getMarked()->format('d.m.Y')?></td>
            <td><?=$exam->getAnnounced()->format('d.m.Y')?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
