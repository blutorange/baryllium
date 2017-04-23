<?php
    use League\Plates\Template\Template;
    use Moose\Entity\Thread;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\Servlet\DocumentServlet;
    use Moose\Util\CmnCnst;
    use Moose\ViewModel\ButtonFactory;
    use Moose\ViewModel\SectionThread;
    /* @var $this Template|PlatesMooseExtension */
    /* @var $thread Thread */
    $this->layout('portal', ['title' => 'Posts']);
    $this->setActiveSection(new SectionThread($thread));
?>

<!-- Dialog delete post -->
<?php $this->insert('partials/component/tc_dialog', [
    'id' => 'dialog_delete_post',
    'title' => 'post.delete.dialog.title',
    'body' => $this->gettext('post.delete.dialog.body'),
    'buttons' => [
        ButtonFactory::makeDeletePost()
            ->addHtmlClass('btn-delete')
            ->setLabelI18n('post.delete.dialog.confirm')
            ->build(),
        ButtonFactory::makeCloseDialog()
            ->addHtmlClass('btn-dialog-close')
            ->setLabelI18n('post.delete.dialog.cancel')
            ->build()
    ]
]); ?>

<!-- Dialog delete thread -->
<?php $this->insert('partials/component/tc_dialog', [
    'id' => 'dialog_delete_thread',
    'title' => 'thread.delete.dialog.title',
    'body' => $this->gettext('thread.delete.dialog.body'),
    'buttons' => [
        ButtonFactory::makeDeleteThread()
            ->addHtmlClass('btn-delete')        
            ->setLabelI18n('post.delete.dialog.confirm')
            ->build(),
        ButtonFactory::makeCloseDialog()
            ->addHtmlClass('btn-dialog-close')        
            ->setLabelI18n('post.delete.dialog.cancel')
            ->build()
    ]
]); ?>

<!-- Navigation -->
<?php $this->insert('partials/component/tc_breadcrumb_sec') ?>

<?php if ($thread !== null): ?>
    <div class="thread-header">
        <!-- Editable thread title -->
        <a href="#"
           class="h1 editable editable-click editable-hint-hover"
           data-type="text"
           data-placeholder="<?=$this->egettext('thread.title.change.placeholder')?>"
           data-title="<?=$this->egettext('thread.title.change')?>"
           data-id="<?=$thread->getId()?>"
           data-save-url="<?=$this->egetResource(CmnCnst::SERVLET_THREAD)?>"
           data-method="PATCH"
           data-field="name"
           data-action="rename"
        >
            <span class="editable-content"><?=$this->e($thread->getName())?></span>
            <span class="editable-hint">(<?=$this->egettext('thread.title.edithint')?>)</span>
        </a>

        <!-- Button delete thread -->
        <?=$this->insert('partials/component/tc_action_button', [
            'button' => ButtonFactory::makeOpenDialog('dialog_delete_thread')
                ->addHtmlClass('btn-delete')
                ->setLabelI18n('button.delete.thread')
                ->addCallbackOnClickData('tid', $thread->getId())
                ->addCallbackOnClickData('redirect', $this->getResource(CmnCnst::PATH_FORUM) . '?fid=' . $thread->getForum()->getId())
                ->build()
        ])?>
    </div>
<?php endif; ?>

<!-- List of posts -->
<div class="wrapper-post jscroll-content jscroll-body counter-main" style="counter-reset:main <?= $postPaginable->getPaginableFirstEntryOrdinal() - 1 ?>;">
    <div class="wrapper-list-post">
        <?php foreach ($postPaginable as $post) { ?>
           <?php $this->insert(CmnCnst::TEMPLATE_TC_POST, [
               'post' => $post
           ]) ?>
        <?php } ?>
    </div>
    
    <?php
    $this->insert(CmnCnst::TEMPLATE_PAGINABLE, [
        'classesContainer' => 'wrapper-nav-post',
        'paginable' => $postPaginable])
    ?> 
</div>

<!-- Pages / Infinite scrolling -->
<?php if ($postPaginable->count() > 0) : ?>
    <h3><?= $this->egettext('thread.new.post') ?></h3>
    <form novalidate
          id="new_post_form"
          method="post"
          data-bootstrap-parsley
          action="<?=$this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF'])?>">

        <?php $this->insert(CmnCnst::TEMPLATE_MARKDOWN, [
            'label'    => 'post.new.content.label',
            'name'     => 'content', 'required' => true,
            'imagePostUrl' => $this->getResource(DocumentServlet::getRoutingPath()) . '?fid=' . $postPaginable[0]->getThread()->getForum()->getId()            
        ]);
        ?> 

        <div class="">
            <button id="threadSubmit" class="btn btn-primary" name="btnSubmit" type="submit">
                <?= $this->egettext('post.new.submit') ?>
            </button>
        </div>
    </form>
<?php else: ?>
    <p><?=$this->egettext('thread.no.posts')?></p>
<?php endif; ?>

