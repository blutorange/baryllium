<?php

use League\Plates\Template\Template;
use Moose\PlatesExtension\PlatesMooseExtension;
    /* @var $this Template|PlatesMooseExtension */
    $lessonList = $lessonList ?? [];
?>

<div id="schedule_separate" class="schedule <?=$carousel ? '' : 'schedule-auto'?>"
     data-header-left="<?=$headerLeft ?? null?>"
     data-header-center="<?=$headerCenter ?? null?>"
     data-header-right="<?=$headerRight ?? null?>"
     data-active-view="<?=$activeView ?? null?>"
     data-height="<?=$height ?? null?>"
>
    <?=$this->egettext('schedule.loading')?>
</div>