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

<ul class="list-group">
    <?php foreach($threadList as $thread){ ?>
        <a href="post.php?<?= Controller\PostController::PARAM_THREAD_ID?>=<?=$thread->getId()?>">
            <li class="list-group-item">
                <span class="badge"><?=$thread->getPostList()->count()?></span>
                <span><?=$thread->getName()?></span>
            </li>
        </a>
    <?php } ?>
</ul>
          
