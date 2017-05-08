<?php

use League\Plates\Template\Template;
use Moose\PlatesExtension\PlatesMooseExtension;
use Moose\Util\CmnCnst;
use Moose\ViewModel\DashboardPanelInterface;
use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */
    /* @var $panel DashboardPanelInterface */
    $this->layout('portal', ['title' => 'Dashboard']);
    $this->setActiveSection(SectionBasic::$DASHBOARD);
?>

<div id="dashboard_panels">
    <?php if (!$this->getCookieOption(CmnCnst::COOKIE_FIELDS, CmnCnst::COOKIE_OPTION_DASHBOARD_VIEW, true)): ?>
        <div class="carousel slide" id="dashboard_carousel" data-ride="carousel" data-pause="hover" data-wrap="true" data-interval="false">
            <!-- Indicators -->
            <ol class="carousel-indicators">
                <?php for ($i = 0; $i < \sizeof($panels); ++$i): ?>
                    <li data-target="#dashboard_carousel" data-slide-to="<?=$i?>" class="<?=$i===0 ? 'active' : ''?>"></li>
                <?php endfor; ?>
            </ol>
            <!-- Wrapper for slides -->
            <div id="dashboard_inner" class="carousel-inner" role="listbox">
                <?php
                    foreach ($panels as $i => $panel): $first = true;
                    $htmlData = $this->serializeHtmlData($panel->getHtmlData());
                    $panel->addData('carousel', true);
                ?>
                    <div class="item <?=$i===0 ? 'active' : ''?>" <?=$htmlData?>>
                        <div class="dashboard-panel panel panel-default" <?=$htmlData?>>
                            <div class="panel-heading">
                                <h3><?=$panel->getLabel()?></h3>
                            </div>
                            <div class="panel-body db-<?=$panel->getClass()?>">
                                <?=$this->insert($panel->getTemplate(), $panels[$i]->getData())?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Controls -->
            <a class="left carousel-control" href="#dashboard_carousel" role="button" data-slide="prev">
              <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
              <span class="sr-only">Previous</span>
            </a>
            <a class="right carousel-control" href="#dashboard_carousel" role="button" data-slide="next">
              <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
              <span class="sr-only">Next</span>
            </a>
        </div>
    
    <?php else: ?>
    
        <div class="container-fluid" id="dashboard">
            <div class="row">
                <?php
                    foreach ($panels as $panel):
                    $panel->addData('carousel', false);
                ?>
                    <div class="dashboard-col col-lg-6">
                        <div class="dashboard-panel panel panel-default" <?=$this->serializeHtmlData($panel->getHtmlData())?>>
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
    
    <?php endif; ?>
</div>