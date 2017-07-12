<?php

use League\Plates\Template\Template;
use Moose\Entity\Exam;
use Moose\PlatesExtension\PlatesMooseExtension;
use Moose\Util\CmnCnst;
use Moose\Util\PermissionsUtil;
use Moose\ViewModel\ButtonFactory;
use Moose\ViewModel\DataTable;
use Moose\ViewModel\DataTableColumn;
use Moose\ViewModel\DataTableColumnInterface;
use Moose\ViewModel\SectionBasic;
    /* @var $examList Exam[] */
    /* @var $this Template|PlatesMooseExtension */    
    $this->layout('portal');
    $this->setActiveSection(SectionBasic::$EXAM);

    $examList = $examList ?? [];    
?>

<fieldset>
    <legend>Offene Prüfungen</legend>
</fieldset>

<fieldset>
    <legend>Angemeldete Prüfungen</legend>
    
    <table id="exam_table" class="table table-stripped table-bordered table-hover">
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
                <td><?=$exam->getMarkString() ?? 'noch nicht benotet'?></td>
                <td><?=$exam->getMarked() === null ? 'unbekannt' : $exam->getMarked()->format('d.m.Y')?></td>
                <td><?=$exam->getAnnounced() === null ? 'unbekannt' : $exam->getAnnounced()->format('d.m.Y')?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!-- TODO: Replace with partials/form/datatable like userlist -->
    <script type="text/javascript">Moose.Library.jQuery('#exam_table').dataTable({pageLength: 100})</script>
</fieldset>
    
<?php if (PermissionsUtil::assertCampusDualForUser(null, false)): ?>
    <?= $this->insert('partials/component/tc_action_button', [
        'button' => ButtonFactory::makeUpdateExam()
            ->setLabelI18n('button.exam.refresh')
            ->addHtmlClass('btn-block')
            ->addCallbackOnClickData('msgConfirm', $this->gettext('confirm.exam.refresh'))
    ])?>
<?php endif; ?>