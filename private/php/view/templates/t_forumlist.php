<?php
    use League\Plates\Template\Template;
    use Moose\ViewModel\SectionBasic;
    use Moose\Util\CmnCnst;
    /* @var $this Template|\Moose\PlatesExtension\MoosePlatesExtension */
    /* @var $this Template */
    $this->layout('portal', ['title' => 'Forums']);
    $this->setActiveSection(SectionBasic::$BOARD);
?>
<div class="cardlist-main">
    <?php foreach($forumList as $forum): ?>
        <div class="cardlist-wrapper col-sm-4">
            <div class="cardlist-card moose-white">
                <a class="cardlist-link" href="forum.php?<?= CmnCnst::URL_PARAM_FORUM_ID?>=<?=$forum->getId()?>">
                    <span class="badge pull-right"><?=$forum->getThreadList()->count()?></span>
                    <?=$forum->getName()?>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>