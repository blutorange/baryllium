<?php
    $id = $id ?? 'dialog';
    $title = $title ?? 'dialog.title';
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
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?=$this->gettext('button.dialog.close')?>
                </button>
            </div>
        </div>
    </div>
</div>