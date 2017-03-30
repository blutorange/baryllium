<?php $this->layout('portal', ['title' => 'Forums', 'activeSection' => Ui\Section::$FORUM]); ?>

<ul class="list-group">
    <?php foreach($forumList as $forum){ ?>
    <a href="./thread.php?<?= Util\CmnCnst::URL_PARAM_FORUM_ID?>=<?=$forum->getId()?>">
            <li class="list-group-item">
                <span class="badge"><?=$forum->getThreadList()->count()?></span>
                <?=$forum->getName()?>
            </li>
        </a>
    <?php } ?>
</ul>