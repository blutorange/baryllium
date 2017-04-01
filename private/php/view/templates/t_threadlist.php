<?php
    use League\Plates\Template\Template;
    use Moose\Servlet\DocumentServlet;
    use Moose\ViewModel\Section;
    use Moose\Util\CmnCnst;
    /* @var $this Template */
    $this->layout('portal', ['title' => 'Threads']);
    $this->setActiveSection(Section::$THREAD);
?>

<?php $this->insert('partials/component/tc_breadcrumb_sec') ?>

<?php if ($threadPaginable->count() === 0): ?>
    <p><?=$this->egettext('forum.no.threads')?></p>
<?php endif; if  ($forum !== null) : ?>
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

        <div class="">
            <button id="threadSubmit" class="btn btn-primary" name="btnSubmit" type="submit">
                <?= $this->egettext('thread.submit') ?>
            </button>
        </div>    
    </form>
<?php endif; ?>

<div class="wrapper-thread jscroll-content jscroll-body counter-main" style="counter-reset:main <?= $threadPaginable->getPaginableFirstEntryOrdinal() - 1 ?>;">
    <ul class="list-group wrapper-list-thread">
        <?php foreach($threadPaginable as $thread){ ?>
            <a href="post.php?<?= CmnCnst::URL_PARAM_THREAD_ID?>=<?=$thread->getId()?>">
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