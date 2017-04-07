<?php
    /* @var $button \Ui\ButtonInterface */
    $link = $button->getLink();
    $dataCallbackClick = $button->getCallbackOnClickData();
    $jsonCallbackClick = sizeof($dataCallbackClick) > 0 ? \json_encode($dataCallbackClick) : '{}';
    $glyphicon = $button->getGlyphicon();
?>
<?php if ($link !== null): ?>
    <a href="<?=$this->e($link)?>"
<?php else: ?>
    <button type="button"
<?php endif; ?>
        id="<?=$this->e($button->getId().$button->getPartialId())?>"
        class="btn <?=$button->getBootstrapClass()?> <?=$button->hasCallbackOnClick() ? 'btn-callback' : ''?>"
        title="<?=$this->e($button->getTitle())?>"
        <?php if ($button->hasCallbackOnClick()): ?>
            data-btn-callback-json="<?=$this->e($jsonCallbackClick)?>"
            data-btn-callback-id="<?=$this->e($button->getId())?>"
        <?php endif; ?>
        <?php foreach ($button->getHtmlAttributes() as $key => $value): ?>
            <?=$key?>="<?=$this->e($value)?>"
        <?php endforeach; ?>
    >
        <?php if (!empty($glyphicon)): ?>
            <span class="glyphicon glyphicon-<?=$glyphicon?>" aria-hidden="true"></span>
        <?php endif;?>
        <?=$button->getLabel()?>
<?php if ($link !== null): ?>
    </a>
<?php else: ?>
    </button>
<?php endif; ?>