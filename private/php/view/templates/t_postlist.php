<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\MoosePlatesExtension;
    use Moose\Servlet\DocumentServlet;
    use Moose\Util\CmnCnst;
    use Moose\ViewModel\SectionThread;
    /* @var $this Template|MoosePlatesExtension */
    $this->layout('portal', ['title' => 'Posts']);
    $this->setActiveSection(new SectionThread($thread));
?>

<?php $this->insert('partials/component/tc_breadcrumb_sec') ?>

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


<?php if ($postPaginable->count() > 0) : ?>
    <h3><?= $this->egettext('thread.new.post') ?></h3>
    <form novalidate
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

