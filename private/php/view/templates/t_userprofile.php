<?php $this->layout('portal', ['title' => 'User profile']); ?>

<?php 
    
    $avatar = $user->getAvatar();
    $tutorialGroup = $user->getTutorialGroup();
    $tutorialGroupName = $tutorialGroup !== null ? $tutorialGroup->getCompleteName() : null;
?>

<p>
    <span><?= $this->e($user->getFirstName())?></span>, <span><?= $this->e($user->getLastName())?></span>
</p>
<p> <?= $this->e($user->getStudentId())?> </p>
<p> <?= $this->e($tutorialGroupName ?? "Unspecified")?> </p>
<?php if ($avatar !== null): ?>
    <img src="<?= $this->e($user->getAvatar())?>"/>
<?php else: ?>
    <p>No avatar set</p>
<?php endif; ?>
