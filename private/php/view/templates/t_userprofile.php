<?php $this->layout('portal', ['title' => 'User profile']); ?>

<?php     
//   $tmp = new \Entity\User();
    $avatar = $user->getAvatar();
    $tutorialGroup = $user->getTutorialGroup();
    $tutorialGroupName = $tutorialGroup !== null ? $tutorialGroup->getCompleteName() : null;
    $fieldOfStudy = $tutorialGroup !== null ? $tutorialGroup->getFieldOfStudy() : null;
    $discipline = $fieldOfStudy !== null ? $fieldOfStudy->getDiscipline() : null;
    $subdiscipline = $fieldOfStudy !== null ? $fieldOfStudy->getSubDiscipline() : null;
?>

<section>
    <div class="moose-white">
        <div class="profile-avatar-area">
            <div id="profile_avatar">
            <?php if ($avatar !== null): ?>
            <img class="avatar" id="profile_avatar_img" src="<?= $this->e($user->getAvatar())?>"/>
            <?php else: ?>
                <p>No avatar set</p>
            <?php endif; ?>
            </div> 
        </div>
        
        <div>  
            <p id="profile_name">
                <span><?= $this->e($user->getFirstName())?></span> <span><?= $this->e($user->getLastName())?></span>
            </p>
            <p class="profile-info-user"> <?= $this->e($user->getStudentId())?> </p>
            <p class="profile-info-user"> <?= $this->e($discipline ?? "Unspecified")?> / <?= $this->e($subdiscipline ?? "Unspecified")?></p>
            <p class="profile-info-user"> <?= $this->e($tutorialGroupName ?? "Unspecified")?> </p>
        </div>  
    </div>


