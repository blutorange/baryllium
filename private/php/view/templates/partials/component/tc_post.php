<?php
    /* @var $post \Entity\Post */
    if ($post === null) {
        error_log('No post given.');
        return;
    }
    $fid = $post->getThread()->getForum()->getId();
    $updateUrl = $this->getResource(\Moose\Servlet\PostServlet::getRoutingPath() . '?pid=' . $post->getId());
    $imagePostUrl = $this->getResource(Moose\Servlet\DocumentServlet::getRoutingPath() . '?fid=' . $fid);
?>
    <div class="panel panel-default counter-main-inc post">
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
        <div class="panel-body post-body">
            <?php if ($post->getUser()->getId() === $this->getUser()->getId()): ?>
                <div data-provide="markdown-loc-editable" 
                     data-update = '.post'
                     data-imageposturl = "<?=$imagePostUrl?>"
                     data-updateurl="<?=$updateUrl?>"
                >
                    <?= $post->getContent() ?>
                </div>
            <?php else : ?>
                <?= $post->getContent() ?>
            <?php endif; ?>
        </div>
    </div>