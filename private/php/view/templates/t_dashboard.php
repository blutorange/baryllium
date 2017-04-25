<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\DashboardPanelInterface;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */
    /* @var $panel DashboardPanelInterface */
    $this->layout('portal', ['title' => 'Dashboard']);
    $this->setActiveSection(SectionBasic::$DASHBOARD);
?>

<table class="moose-datatable" data-url="<?=$this->egetResource(\Moose\Util\CmnCnst::SERVLET_USER)?>" data-action="list">
        <thead>
            <tr>
                <th>date</th>
                <th>first name</th>
                <th>last name</th>
                <th>sid</th>
            </tr>
        </thead>
</table>

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