<?php
    use League\Plates\Template\Template;
    use Moose\Entity\Thread;
    use Moose\PlatesExtension\MoosePlatesExtension;
    use Moose\Servlet\DocumentServlet;
    use Moose\Util\CmnCnst;
    use Moose\ViewModel\Paginable;
    use Moose\ViewModel\SectionForum;
    /* @var $this Template|MoosePlatesExtension */
    /* @var $threadPaginable Paginable */
    /* @var $thread Thread */
    $this->layout('portal', ['title' => 'Threads']);
    $this->setActiveSection(new SectionForum($forum));
?>

<?php $this->insert('partials/component/tc_breadcrumb_sec') ?>

<!-- List of threads -->
<div class="wrapper-thread jscroll-content jscroll-body counter-main" style="counter-reset:main <?= $threadPaginable->getPaginableFirstEntryOrdinal() - 1 ?>;">
    <ul class="list-group wrapper-list-thread">
        <?php foreach($threadPaginable as $thread){ ?>
            <li class="list-group-item">
                <a class="d-block forum-thread" href="<?=$this->egetResource(CmnCnst::PATH_THREAD)?>?<?= CmnCnst::URL_PARAM_THREAD_ID?>=<?=$thread->getId()?>">
                    <span class="thread"><?=$thread->getName()?></span>
                    <span class="badge pull-right"><?=$thread->getPostList()->count()?></span>
                </a>
            </li>
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
    
    <div class="new-thread">
    <h3><?=$this->egettext('forum.new.thread')?></h3>
    <form id="new_thread_form" novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">  
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
        </div>   
    

        <!-- Show navigation. -->
        <div class="">
            <button id="threadSubmit" class="btn btn-default" name="btnSubmit" type="submit">
                <?= $this->egettext('thread.submit') ?>
            </button>
        </div>    
    </form>
<?php endif; ?>
