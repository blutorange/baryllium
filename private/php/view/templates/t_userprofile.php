<?php $this->layout('portal', ['title' => 'User profile']); ?>

<?php
//   $tmp = new \Entity\User();
$avatar = $user->getAvatar();
$tutorialGroup = $user->getTutorialGroup();
$tutorialGroupName = $tutorialGroup !== null ? $tutorialGroup->getCompleteName()
            : null;
$fieldOfStudy = $tutorialGroup !== null ? $tutorialGroup->getFieldOfStudy() : null;
$discipline = $fieldOfStudy !== null ? $fieldOfStudy->getDiscipline() : null;
$subdiscipline = $fieldOfStudy !== null ? $fieldOfStudy->getSubDiscipline() : null;
?>

<p>
    <span><?= $this->e($user->getFirstName()) ?></span>, <span><?= $this->e($user->getLastName()) ?></span>
</p>
<p> <?= $this->e($user->getStudentId()) ?> </p>
<p> <?= $this->e($discipline ?? "Unspecified") ?> / <?=
    $this->e($subdiscipline ?? "Unspecified")
    ?></p>
<p> <?= $this->e($tutorialGroupName ?? "Unspecified") ?> </p>
    <form>
        <?php 
            $this->insert('partials/form/checkbox', [
            'label' => 'option.paging.list.label',
            'name' => 'option.paging.list',
            'persistClient' => true
            ])
        ?>
    </form>
<?php if ($avatar !== null): ?>
    <img class="avatar" src="<?= $this->e($user->getAvatar()) ?>"/>
<?php else: ?>
    <p>No avatar set</p>
<?php endif; ?>