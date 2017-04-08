<?php

use League\Plates\Template\Template;
use Moose\Entity\Post;
use Moose\PlatesExtension\PlatesMooseExtension;
use Moose\Servlet\DocumentServlet;
use Moose\Servlet\PostServlet;
use Moose\Util\CmnCnst;
    /* @var $post Post */
    /* @var $this Template|PlatesMooseExtension */
    if ($post === null) {
        \error_log('No post given.');
        return;
    }
    $fid = $post->getThread()->getForum()->getId();
    $updateUrl = $this->getResource(PostServlet::getRoutingPath() . '?pid=' . $post->getId());
    $imagePostUrl = $this->getResource(DocumentServlet::getRoutingPath() . '?fid=' . $fid);
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
                <?php $this->insert('partials/component/tc_action_button', [
                    'button' => ButtonMarkdownEdit::make('.panel', '.post-body')
                        ->setTitleI18n('post.nav.edit')
                        ->setGlyphicon('edit')
                        ->build()
                    ])
                ?>            
                <?php $this->insert('partials/component/tc_action_button', [
                    'button' => ButtonOpenDialog::make('dialogDeleteEntity', true)
                        ->addCallbackOnClickData('pid', $post->getId())
                        ->setTitleI18n('post.nav.delete')
                        ->setGlyphicon('remove')
                        ->build()
                    ])
                ?>
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