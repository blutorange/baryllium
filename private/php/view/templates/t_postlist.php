<?php $this->layout('portal', ['title' => 'Posts']); ?>

<?php foreach ($postList as $post) { ?>        
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <?php if ($post->getUser() !== null) : ?>
                    <img class="avatar" width="16" src="<?= $this->e($post->getUser()->getAvatar())?>">
                    <?= $this->e($post->getUser()->getFirstName() ?? 'Anonymous') ?> <?= $this->e($post->getUser()->getLastName() ?? 'Anonymous') ?>
                <?php endif; ?>
            </h3>
        </div>
        <div class="panel-body">
            <?= $post->getContent() ?>
        </div>
    </div>
<?php } ?>

<h3><?=$this->egettext('post.write.new')?></h3>

<form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action
                    ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
    <?php
    $this->insert('partials/form/input',['label' => 'post.new.title.label',
        'name' => 'title', 'required' => true])
    ?>   

    <?php
    $this->insert('partials/form/markdown',['label' => 'post.new.content.label',
        'name' => 'content', 'required' => true])
    ?> 

    <div class="">
        <button id="threadSubmit" class="btn btn-primary" name="btnSubmit" type="submit">
            <?= $this->egettext('post.new.submit') ?>
        </button>
    </div>
</form>          
