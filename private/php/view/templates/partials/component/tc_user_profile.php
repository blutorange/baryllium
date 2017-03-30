<?php
    $tutorialGroup = $user->getTutorialGroup();
    $tutorialGroupName = $tutorialGroup !== null ? $tutorialGroup->getCompleteName() : null;
    $fieldOfStudy = $tutorialGroup !== null ? $tutorialGroup->getFieldOfStudy() : null;
    $discipline = $fieldOfStudy !== null ? $fieldOfStudy->getDiscipline() : null;
    $subdiscipline = $fieldOfStudy !== null ? $fieldOfStudy->getSubDiscipline() : null;
?>
<div class="moose-white profile">  
    <p class="profile-name">
        <span><?= $this->e($user->getFirstName()) ?></span> <span><?= $this->e($user->getLastName()) ?></span>
    </p>
    <p class="profile-info-user">
        <?= $this->egettext('profile.studentid') ?>: 
        s<?= $this->e($user->getStudentId()) ?>
    </p>
    <p class="profile-info-user">
        <?= $this->egettext('profile.fieldofstudy') ?>:
        <?= $this->e($discipline ?? $this->gettext('profile.fieldofstudy.discipline.none')) ?> / <?= $this->e($subdiscipline
                            ?? $this->gettext('profile.fieldofstudy.subdiscipline.none')) ?>
    </p>
    <p class="profile-info-user">
        <?= $this->egettext('profile.tutorialgroup') ?>:
        <?= $this->e($tutorialGroupName ?? $this->gettext('profile.tutorialgroup.none')) ?>
    </p>
    <p class="profile-info-user">
        <?= $this->egettext('profile.postcount') ?>:
        <?= $postCount ?>
    </p>
</div>  