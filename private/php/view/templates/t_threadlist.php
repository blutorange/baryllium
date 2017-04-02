<?php

use League\Plates\Template\Template;
use Moose\PlatesExtension\MainExtension;
use Moose\Servlet\DocumentServlet;
use Moose\Util\CmnCnst;
use Moose\ViewModel\SectionForum;
    /* @var $this Template|MainExtension */
    $this->layout('portal', ['title' => 'Threads']);
    $this->setActiveSection(new SectionForum($forum));
?>

<?php $this->insert('partials/component/tc_breadcrumb_sec') ?>

<!-- List of threads -->
<div class="wrapper-thread jscroll-content jscroll-body counter-main" style="counter-reset:main <?= $threadPaginable->getPaginableFirstEntryOrdinal() - 1 ?>;">
    <ul class="list-group wrapper-list-thread">
        <?php foreach($threadPaginable as $thread){ ?>
            <a href="thread.php?<?= CmnCnst::URL_PARAM_THREAD_ID?>=<?=$thread->getId()?>">
                <li class="list-group-item">
                    <span class="badge"><?=$thread->getPostList()->count()?></span>
                    <span><?=$thread->getName()?></span>
                </li>
            </a>
        <?php } ?>
    </ul>

    <?php
    $this->insert('partials/component/paginable', [
        'classesContainer' => 'wrapper-nav-thread',
        'paginable' => $threadPaginable])
    ?> 
</div>

<?php if ($threadPaginable->count() === 0): ?>
    <p><?=$this->egettext('forum.no.threads')?></p>
<?php endif; if  ($forum !== null) : ?>    
    <!-- Editor for new threads. -->
    <h3><?=$this->egettext('forum.new.thread')?></h3>
    <form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">  
        <?php $this->insert('partials/form/input', ['label' => 'thread.add',
            'name' => 'title', 'required' => true, 'kind' => 'textarea',
            'attributes' => 'data-provide="markdown"'])
        ?>
        <?php 
            if ($forum !== null) {
                $this->insert('partials/form/markdown', [
                    'label' => 'post.new.content.label',
                    'name' => 'content', 'required' => true,
                    'imagePostUrl' => $this->getResource(DocumentServlet::getRoutingPath()) . '?fid=' . $forum->getId()
                ]);
            }
        ?> 

        <!-- Show navigation. -->
        <div class="">
            <button id="threadSubmit" class="btn btn-primary" name="btnSubmit" type="submit">
                <?= $this->egettext('thread.submit') ?>
            </button>
        </div>    
    </form>
<?php endif; ?>