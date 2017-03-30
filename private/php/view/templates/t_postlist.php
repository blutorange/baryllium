<?php
    $this->layout('portal', ['title' => 'Posts', 'activeSection' => Ui\Section::$POST]);
    use Util\CmnCnst;
?>

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

<h3><?= $this->egettext('post.write.new') ?></h3>

<form novalidate
      method="post"
      data-bootstrap-parsley
      action="<?=$this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF'])?>">

    <?php
    if ($postPaginable->count() > 0) {
        $this->insert(CmnCnst::TEMPLATE_MARKDOWN, [
            'label'    => 'post.new.content.label',
            'name'     => 'content', 'required' => true,
            'imagePostUrl' => $this->getResource(Moose\Servlet\DocumentServlet::getRoutingPath()) . '?fid=' . $postPaginable[0]->getThread()->getForum()->getId()            
        ]);
    }
    ?> 

    <div class="">
        <button id="threadSubmit" class="btn btn-primary" name="btnSubmit" type="submit">
            <?= $this->egettext('post.new.submit') ?>
        </button>
    </div>
</form>          
