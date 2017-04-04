<?php
    use League\Plates\Template\Template;
    use Moose\ViewModel\SectionBasic;
    use Moose\Util\CmnCnst;
    /* @var $this Template|\Moose\PlatesExtension\MoosePlatesExtension */
    /* @var $this Template */
    $this->layout('portal', ['title' => 'Forums']);
    $this->setActiveSection(SectionBasic::$BOARD);
?>

<ul class="list-group">
    <?php foreach($forumList as $forum){ ?>
    <a href="forum.php?<?= CmnCnst::URL_PARAM_FORUM_ID?>=<?=$forum->getId()?>">
        <li class="list-group-item">
            <span class="badge"><?=$forum->getThreadList()->count()?></span>
            <?=$forum->getName()?>
        </li>
    </a>
    <?php } ?>
</ul>