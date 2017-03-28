<?php $this->layout('portal', ['title' => 'Posts']); ?>

<div class="wrapper-post jscroll-content jscroll-body counter-main" style="counter-reset:main <?= $postPaginable->getPaginableFirstEntryOrdinal() - 1 ?>;">
    <div class="wrapper-list-post">
        <?php foreach ($postList as $post) { ?>        
            <div class="panel panel-default counter-main-inc">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <?php if ($post->getUser() !== null) : ?>
                            <span class="count-post counter-main-after">#</span>
                            <img class="avatar" width="16" src="<?= $this->e($post->getUser()->getAvatar()) ?>">
                            <span>
                                <?= $this->e($post->getUser()->getFirstName() ?? 'Anonymous') ?>
                                <?= $this->e($post->getUser()->getLastName() ?? 'Anonymous') ?>
                            </span>
                            <span>
                                <?php if ($post->getEditTime() !== null) : ?>
                                    (<?=$this->egettext('post.last.edited') ?> <?= $this->e($post->getEditTime()->format($this->gettext('default.datetime.format'))) ?>)
                                <?php else : ?>
                                    (<?= $this->e($post->getCreationTime()->format($this->gettext('default.datetime.format'))) ?>)
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <?php if ($post->getUser()->getId() === $this->getUser()->getId()): ?>
                        <div data-provide="markdown-loc-editable" data-updateurl="<?= $this->getResource('public/servlet/updatePost.php?pid=' . $post->getId()) ?>">
                            <?= $post->getContent() ?>
                        </div>
                    <?php else : ?>
                        <?= $post->getContent() ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php } ?>
    </div>
    
    <?php
    $this->insert('partials/component/paginable', [
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
    $this->insert('partials/form/markdown', [
        'label'    => 'post.new.content.label',
        'name'     => 'content', 'required' => true])
    ?> 

    <div class="">
        <button id="threadSubmit" class="btn btn-primary" name="btnSubmit" type="submit">
            <?= $this->egettext('post.new.submit') ?>
        </button>
    </div>
</form>          
