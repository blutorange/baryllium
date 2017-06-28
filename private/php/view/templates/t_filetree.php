<?php
use League\Plates\Template\Template;
use Moose\PlatesExtension\PlatesMooseExtension;
use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */    
    $this->layout('portal');
    $this->setActiveSection(SectionBasic::$FILETREE);
?>

<h1><?=$this->egettext('filetree.heading')?></h1>

<div class="row">
    <div class="col-md-3">
        <div id="filetree_top"
             class="filetree filetree-auto"
             data-root-title="<?=$this->egettext('filetree.root.node')?>"
             >
            <ul id="treeData" style="display: none;">
            </ul>
        </div>
    </div>

    <div class="col-md-6">
        <div id="filetree_details">
            <h2 class="f-documentTitle"></h2>
            <a href target="_blank" id="f-preview">
               <img src alt class="img-fluid">
            </a>
            <img src alt id="f-preview"/>
            <table class="table table-striped">
                <tr>
                    <td><?=$this->egettext('filetree.title')?></td>
                    <td class="f-documentTitle"></td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.description')?></td>
                    <td class="f-description"></td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.filename')?></td>
                    <td class="f-fileName"></td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.create.time')?></td>
                    <td class="f-createTime"></td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.mime')?></td>
                    <td class="f-mime"></td>
                </tr>
            </table>
        </div>    
    </div>
</div>
