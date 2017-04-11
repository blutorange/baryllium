<?php
    use League\Plates\Template\Template;
    use Moose\Entity\User;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\Servlet\DocumentServlet;
    use Moose\Util\CmnCnst;
    use Moose\ViewModel\Paginable;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */
    /* @var $userPaginable Paginable */
    /* @var $user User */
    $this->layout('portal', ['title' => 'Posts']);
    $this->setActiveSection(SectionBasic::$USERLIST);
?>

<?php $this->insert('partials/component/tc_breadcrumb_sec') ?>

<!-- List of users -->
<div class="wrapper-userlist jscroll-body counter-main">
    <h1><?=$this->egettext('userlist-caption')?></h1>
    <table class="jscroll-content table table-hover table-responsive table-striped wrapper-list-user ">
        <thead>
            <tr>
                <th><?=$this->egettext('userlist.head.avatar')?></th>
                <th><?=$this->egettext('userlist.head.membersince')?></th>
                <th><?=$this->egettext('userlist.head.name')?></th>
                <th><?=$this->egettext('userlist.head.sid')?></th>
                <th><?=$this->egettext('userlist.head.tutgroup')?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="5">
                    <?php
                    $this->insert('partials/component/paginable', [
                        'classesContainer' => 'tbody-userlist',
                        'paginable' => $userPaginable])
                    ?> 
                </td>
            </tr>
        </tfoot>
        <tbody class="">
            <?php foreach($userPaginable as $user): ?>
                <tr class="">
                    <td>
                        <img alt="user profile image" src="<?=$user->getAvatar()?>"/>
                    </td>
                    <td>
                        <?=$this->edate($user->getRegDate())?>
                    </td>
                    <td>
                        <a class="d-block" href="userprofile.php?<?= CmnCnst::URL_PARAM_USER_ID?>=<?=$user->getId()?>">
                            <?=$user->getFirstName()?> <?=$user->getLastName()?>
                        </a>
                    </td>
                    <td>
                        <span>s<?=$user->getStudentId()?></span>
                    </td>
                    <td>
                        <span class="badge"><?=$user->getTutorialGroup()->getCompleteName()?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
