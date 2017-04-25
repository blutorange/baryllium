<?php

use Moose\Entity\User;
use Moose\Util\CmnCnst;
use Moose\ViewModel\ButtonFactory;
    /* @var $user User */
    $tutorialGroup = $user->getTutorialGroup();
    $tutorialGroupName = $tutorialGroup !== null ? $tutorialGroup->getCompleteName() : null;
    $fieldOfStudy = $tutorialGroup !== null ? $tutorialGroup->getFieldOfStudy() : null;
    $discipline = $fieldOfStudy !== null ? $fieldOfStudy->getDiscipline() : null;
    $subdiscipline = $fieldOfStudy !== null ? $fieldOfStudy->getSubDiscipline() : null;
    $mail = $user->getMail() ?? null;
?>
<div class="moose-white profile">  
    <p class="profile-name">
        <span><?= $this->e($user->getFirstName()) ?></span> <span><?= $this->e($user->getLastName()) ?></span>
    </p>
    <p class="profile-info-user profile-sid">
        <?= $this->egettext('profile.studentid') ?>: 
        s<?= $this->e($user->getStudentId()) ?>
    </p>
    <p class="profile-info-user profile-fos">
        <?= $this->egettext('profile.fieldofstudy') ?>:
        <?= $this->e($discipline ?? $this->gettext('profile.fieldofstudy.discipline.none')) ?> / <?= $this->e($subdiscipline
                            ?? $this->gettext('profile.fieldofstudy.subdiscipline.none')) ?>
    </p>
    <p class="profile-info-user profile-tutgroup">
        <?= $this->egettext('profile.tutorialgroup') ?>:
        <?= $this->e($tutorialGroupName ?? $this->gettext('profile.tutorialgroup.none')) ?>
    </p>
    <p class="profile-info-user profile-mail">
        <?= $this->egettext('profile.mail') ?>:
        
        <a href="#"
            title="<?=$this->egettext('user.mail.change')?>"
            class="editable editable-click"
            data-type="text"
            data-placeholder="<?=$this->egettext('user.mail.change.placeholder')?>"
            data-id="<?=$user->getId()?>"
            data-save-url="<?=$this->egetResource(CmnCnst::SERVLET_USER)?>"
            data-method="PATCH"
            data-field="mail"
            data-action="changeMail"
            data-emptytext="<?=$this->egettext('profile.mail.unknown')?>"
         ><?= $this->e($mail) ?></a>
    </p>
    <p class="profile-info-user profile-postcount">
        <?= $this->egettext('profile.postcount') ?>:
        <?= $postCount ?>
    </p>
    
    <?=$this->insert('partials/component/tc_action_button', [
        'button' => ButtonFactory::makeUploadAvatar()
            ->addHtmlClass('center-block')
            ->setLabelI18n('profile.avatar.upload')
            ->build()
    ])?>
    
    <form class="hidden" id="user_profile_form"
          enctype="multipart/form-data" method="post"
          action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
        <input id="avatar_upload" name="avatar" type="file" required/>
        <input type="hidden" name="_avatar"/>
        <input name="btnSubmit" type="submit"/>
    </form>
</div>  