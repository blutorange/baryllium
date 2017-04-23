<?php
    use League\Plates\Template\Template;
    use Moose\ViewModel\SectionBasic;
    use Moose\Util\CmnCnst;
    /* @var $this Template|\Moose\PlatesExtension\MoosePlatesExtension */
    /* @var $this Template */
    $this->layout('portal', ['title' => 'Forums']);
    $this->setActiveSection(SectionBasic::$BOARD);
?>

<div id="wrapper_forum">
    <ul class="list-group">
        <?php foreach($forumList as $forum){ ?>
            <a class="board-forum" href="forum.php?<?= CmnCnst::URL_PARAM_FORUM_ID?>=<?=$forum->getId()?>">
                <li class="list-group-item">
                    <span class="badge"><?=$forum->getThreadList()->count()?></span>
                    <span class="forum"><?=$forum->getName()?></span>
                </li>
            </a>
        <?php } ?>
    </ul>
</div>