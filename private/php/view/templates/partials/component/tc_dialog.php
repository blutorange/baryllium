<?php

use League\Plates\Template\Template;
use Moose\PlatesExtension\PlatesMooseExtension;
use Ui\ButtonInterface;
    /* @var $this Template|PlatesMooseExtension */
    /* @var $buttons ButtonInterface[] */
    $id = $id ?? 'dialog';
    $title = $title ?? 'dialog.title';    
    $buttons = $buttons ?? [];
    $body = $body ?? '';
?>
<div id="<?=$id?>" class="modal fade" role="dialog" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" class="close">&times;</button>
                <h4 class="modal-title"><?=$this->egettext($title)?></h4>
            </div>
            <div class="modal-body">
                <?= $body ?>
            </div>
            <div class="modal-footer">
                <?php foreach ($buttons as $button) : ?>
                    <?php $this->insert('partials/component/tc_action_button', ['button' => $button]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>