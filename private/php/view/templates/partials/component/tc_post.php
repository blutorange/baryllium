<?php
    /* @var $post \Moose\Entity\Post */
    if ($post === null) {
        error_log('No post given.');
        return;
    }
    $fid = $post->getThread()->getForum()->getId();
    $updateUrl = $this->getResource(\Moose\Servlet\PostServlet::getRoutingPath() . '?pid=' . $post->getId());
    $imagePostUrl = $this->getResource(Moose\Servlet\DocumentServlet::getRoutingPath() . '?fid=' . $fid);
    $isAuthor = $post->getUser()->getId() === $this->getUser()->getId();
?>
<div class="panel panel-default counter-main-inc post">
    <div class="panel-heading">
        <h3 class="panel-title pull-left">
            <span class="">
                <span class="count-post counter-main-after">#</span>
                <?php if ($post->getUser() !== null) : ?>
                    <img class="avatar" width="16" src="<?= $this->e($post->getUser()->getAvatar()) ?>">
                    <span>
                        <?= $this->e($post->getUser()->getFirstName() ?? 'Anonymous') ?>
                        <?= $this->e($post->getUser()->getLastName() ?? 'Anonymous') ?>
                    </span>
                <?php endif; ?>
                <span>
                    <?php if ($post->getEditTime() !== null) : ?>
                        (<?=$this->egettext('post.last.edited') ?> <?= $this->e($post->getEditTime()->format($this->gettext('default.datetime.format'))) ?>)
                    <?php else : ?>
                        (<?= $this->e($post->getCreationTime()->format($this->gettext('default.datetime.format'))) ?>)
                    <?php endif; ?>
                </span>
            </span>
        </h3>
        <div class="pull-right btn-group" role="group" aria-label="Post options: delete, permalink etc.">
            <?php if ($isAuthor) : ?>
            <button title="<?=$this->egettext('post.nav.edit')?>" type="button" class="btn btn-default">
                <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
            </button>
            <button title="<?=$this->egettext('post.nav.delete')?>" type="button" class="btn btn-default">
                <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
            </button>
            <?php endif; ?>
            <button title="<?=$this->egettext('post.nav.permlink')?>" type="button" class="btn btn-default">
                <span class="glyphicon glyphicon-link" aria-hidden="true"></span>
            </button>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php if ($isAuthor): ?>
        <div class="panel-body post-body"
            data-provide="markdown-loc-editable"
            data-update = '.post'
            data-editable = '.post-md-trigger'
            data-imageposturl = "<?=$imagePostUrl?>"
            data-updateurl="<?=$updateUrl?>"
        >
            <div class="post-md-trigger" >
                <?= $post->getContent() ?>
            </div>
        </div>
    <?php else : ?>
        <div class="panel-body post-body">
            <?= $post->getContent() ?>
        </div>
    <?php endif; ?>
</div>