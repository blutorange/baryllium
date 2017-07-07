<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\ButtonFactory;
    use Moose\ViewModel\ButtonInterface;
    /* @var $this Template|PlatesMooseExtension */
    /* @var $buttons ButtonInterface[] */
    $id = $id ?? 'dialog';
    $title = $title ?? 'dialog.title';    
    $dismissButton = $dismissButton ?? ButtonFactory::makeDismissDialog();
    $buttons = $buttons ?? [];
    $body = $body ?? '';
?>
<div id="<?=$id?>" class="modal fade " role="dialog" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <?php $this->insert('partials/component/tc_action_button', ['button' => $dismissButton]) ?>
                <h4 class="modal-title"><?=$this->egettext($title)?></h4>
            </div>
            <div class="modal-main-wrapper">
                <div class="modal-body">
                    <?php if (\is_array($body)) {
                        foreach ($body as $html) {
                            echo $html;
                        }
                    } else {
                        echo $body;
                    }
                    ?>
                </div>
                <div class="modal-footer">
                    <?php foreach ($buttons as $button) : ?>
                        <?php $this->insert('partials/component/tc_action_button', ['button' => $button]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>