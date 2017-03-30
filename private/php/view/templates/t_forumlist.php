<?php
    use League\Plates\Template\Template;
    use Ui\Section;
    use Util\CmnCnst;
    /* @var $this Template */
    $this->layout('portal', ['title' => 'Forums']);
    $this->setActiveSection(Section::$FORUM);
?>

<ul class="list-group">
    <?php foreach($forumList as $forum){ ?>
    <a href="./thread.php?<?= CmnCnst::URL_PARAM_FORUM_ID?>=<?=$forum->getId()?>">
            <li class="list-group-item">
                <span class="badge"><?=$forum->getThreadList()->count()?></span>
                <?=$forum->getName()?>
            </li>
        </a>
    <?php } ?>
</ul>