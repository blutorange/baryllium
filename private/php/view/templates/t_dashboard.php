<?php

    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\Util\CmnCnst;
    use Moose\ViewModel\DashboardPanelInterface;
    use Moose\ViewModel\DataTable;
    use Moose\ViewModel\DataTableColumn;
    use Moose\ViewModel\SectionBasic;
    use Moose\ViewModel\DataTableColumninterface as Col;

    /* @var $this Template|PlatesMooseExtension */
    /* @var $panel DashboardPanelInterface */
    $this->layout('portal', ['title' => 'Dashboard']);
    $this->setActiveSection(SectionBasic::$DASHBOARD);
?>

<?php $this->insert('partials/form/datatable', [
    'table' => DataTable::builder('userlist_table')
        ->setRelativeUrl(CmnCnst::SERVLET_USER)
        ->setIsSearchable(true)
        ->setSearchDelay(1000)
        ->addColumn(DataTableColumn::builder('avatar')->title('userlist.head.avatar')->setIsOrderable(false)->image())
        ->addColumn(DataTableColumn::builder('regDate')->title('userlist.head.membersince')->order()->date())
        ->addColumn(DataTableColumn::builder('firstName')->title('userlist.head.firstname')->order()->search())
        ->addColumn(DataTableColumn::builder('lastName')->title('userlist.head.lastname')->order())
        ->addColumn(DataTableColumn::builder('studentId')->title('userlist.head.studentid')->order()->setType(Col::TYPE_STUDENTID))
        ->addColumn(DataTableColumn::builder('tutorialGroup')->title('userlist.head.tutgroup')->order()->badge()),
])?>

<pre>
--main template
DataTableColumn::builder()->setTemplate('partials/components/tc_userlist_tutgroup', $variableName = 'value')
DataTableColumn::builder()->setTemplate('partials/components/tc_userlist_tutgroup', $proccessingFunction)

-- Servlet.php
Servlet param: template=name, use whitelist

-- 206-moose-datatable.js
ajaxServlet(..., ..., {
    render: {
        studentid: { render: true }
    }
});

$processingFunction.set($cell, value)
$processingFunction = {
    set: function($cell, value) {
        $cell.find('.content').text(value);
        $cell.find('.content').text("s" + value);
        $cell.find('img').attr('href', value);
    }
}

</pre>

<div class="container" id="dashboard">
    <div class="row">
        <?php foreach ($panels as $panel): ?>
            <div class="dashboard-col col-md-6">
                <div class="dahsboard-panel panel panel-default">
                    <div class="panel-heading">
                        <h3><?=$panel->getLabel()?></h3>
                    </div>
                    <div class="panel-body db-<?=$panel->getClass()?>">
                        <?=$this->insert($panel->getTemplate(), $panel->getData())?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>