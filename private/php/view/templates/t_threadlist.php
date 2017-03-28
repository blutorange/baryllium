<?php $this->layout('portal', ['title' => 'Threads']); ?>

<form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">  
    
    
    <?php $this->insert('partials/form/input', ['label' => 'thread.add',
        'name' => 'title', 'required' => true, 'kind' => 'textarea',
        'attributes' => 'data-provide="markdown"'])
    ?>
    
    <?php $this->insert('partials/form/markdown', [
        'label' => 'post.new.content.label',
        'name' => 'content', 'required' => true])
    ?> 
    
    <div class="">
        <button id="threadSubmit" class="btn btn-primary" name="btnSubmit" type="submit">
            <?= $this->egettext('thread.submit') ?>
        </button>
    </div>    
</form>

<div class="wrapper-thread jscroll-content jscroll-body counter-main" style="counter-reset:main <?= $threadPaginable->getPaginableFirstEntryOrdinal() - 1 ?>;">
    <ul class="list-group wrapper-list-thread">
        <?php foreach($threadList as $thread){ ?>
            <a href="post.php?<?= Controller\PostController::PARAM_THREAD_ID?>=<?=$thread->getId()?>">
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
          
