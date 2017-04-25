<?php
    use League\Plates\Template\Template;
    use Moose\ViewModel\SectionBasic;
    use Moose\Util\CmnCnst;
    /* @var $this Template|\Moose\PlatesExtension\MoosePlatesExtension */
    /* @var $this Template */
    $this->layout('portal', ['title' => 'Forums']);
    $this->setActiveSection(SectionBasic::$BOARD);
?>

<div class="cardlist-main" id="forumlist_wrapper">
    <?php foreach($forumList as $forum): ?>
        <ul class="cardlist-wrapper col-sm-4">
            <li class="cardlist-card moose-white">
                <a class="cardlist-link" href="forum.php?<?= CmnCnst::URL_PARAM_FORUM_ID?>=<?=$forum->getId()?>">
                    <span class="cardlist-text"><?=$forum->getName()?></span>
                    <span class="badge pull-right"><?=$forum->getThreadList()->count()?></span>
                </a>
            </li>
        </ul>
    <?php endforeach; ?>
</div>