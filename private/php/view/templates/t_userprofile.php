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
        <label for="infinite-scrolling"><?= $this->egettext('infinitescrolling.enable') ?></label>
        <input type="checkbox" id="infinite-scrolling" name="infinite-scrolling">
    </form>
<?php if ($avatar !== null): ?>
    <img class="avatar" src="<?= $this->e($user->getAvatar()) ?>"/>
<?php else: ?>
    <p>No avatar set</p>
<?php endif; ?>
<script type="text/javascript">
    $(document).ready(function () {
        if (window.moose.getFromLocalStorage() === "1") {
            $("input[name=infinite-scrolling]").prop('checked', true);
        } else {
            $("input[name=infinite-scrolling]").prop('checked', false);
        }

        $("input[name=infinite-scrolling]").click(function () {
            if ($("input[name=infinite-scrolling]").prop('checked')) {
                localStorage['infinite-scrolling'] = 0;
            } else {
                localStorage['infinite-scrolling'] = 1;
            }
        });
    });
</script>
<?php
