<?php
use League\Plates\Template\Template;
use Moose\PlatesExtension\PlatesMooseExtension;
use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */    
    $this->layout('portal');
    $this->setActiveSection(SectionBasic::$FILETREE);
?>

<h1><?=$this->egettext('filetree.heading')?></h1>

<div id="filetree_top"
     class="filetree filetree-auto"
     data-root-title="<?=$this->egettext('filetree.root.node')?>"
     >
    <!-- TODO Actual data, either as HTML or JSON. -->
    <ul id="treeData" style="display: none;">
        <li id="1">Node 1
        <li id="2" class="expanded folder">Folder 2
        <ul>
            <li id="3">Node 2.1
            <li id="4">Node 2.2
        </ul>
        <li id="k234" class="lazy folder">This is a lazy loading folder with key k234.</li>
    </ul>
</div>

<div id="filetree_details">
    Here are details, file info etc.
    Filename: <span id="ftd_filename"></span>
</div>