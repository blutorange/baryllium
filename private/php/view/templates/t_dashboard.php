<?php
    use League\Plates\Template\Template;
    use Moose\ViewModel\Section;
    /* @var $this Template */
    $this->layout('portal', ['title' => 'Dashboard']);
    $this->setActiveSection(Section::$DASHBOARD);
?>


<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3>Quarterly Sales</h3>
                </div>
                <div class="panel-body">
                    <div id="quarterChart"></div>
                        <span style="font-size:128px;" class="glyphicon glyphicon-book" aria-hidden="true"></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3>Dont use inline styling!!</h3>
                </div>
                <div id="detailChart" class="panel-body">
                    <span style="font-size:128px;" class="glyphicon glyphicon-search" aria-hidden="true"></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3>Replace with some real layout</h3>
                </div>
                <div id="detailChart" class="panel-body">
                    <span style="font-size:128px;" class="glyphicon glyphicon-equalizer" aria-hidden="true"></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3>Just for testing</h3>
                </div>
                <div id="detailChart" class="panel-body">
                    <span style="font-size:128px;" class="glyphicon glyphicon-tower" aria-hidden="true"></span>
                </div>
            </div>
        </div>
    </div>
</div>