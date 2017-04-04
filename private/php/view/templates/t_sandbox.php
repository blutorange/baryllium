<?php

use League\Plates\Template\Template;
use Moose\PlatesExtension\PlatesMooseExtension;
use Moose\ViewModel\SectionBasic;
use Odan\Asset\PlatesAssetExtension;
    /* @var $this Template|PlatesMooseExtension|PlatesAssetExtension */
    $this->layout('portal', ['title' => 'Dashboard']);
    $this->setActiveSection(SectionBasic::$DASHBOARD);
?>

<pre>
<?=$this->e($this->inlineScript('resource/bootstrap/css/bootstrap.min.css'))?>
</pre>