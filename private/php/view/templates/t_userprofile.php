<?php $this->layout('portal', ['title' => 'User profile']); ?>

<?php
$avatar = $user->getAvatar();
$tutorialGroup = $user->getTutorialGroup();
$tutorialGroupName = $tutorialGroup !== null ? $tutorialGroup->getCompleteName()
            : null;
$fieldOfStudy = $tutorialGroup !== null ? $tutorialGroup->getFieldOfStudy() : null;
$discipline = $fieldOfStudy !== null ? $fieldOfStudy->getDiscipline() : null;
$subdiscipline = $fieldOfStudy !== null ? $fieldOfStudy->getSubDiscipline() : null;
?>

<div class="container">
    <?php if ($avatar !== null): ?>
        <img class="avatar center-block" src="<?= $this->e($user->getAvatar()) ?>"/>
    <?php else: ?>
        <p class="center-block text-center">No avatar set</p>
    <?php endif; ?>
    <span class="center-block text-center">
       <?= $this->e($user->getFirstName()) ?>, <?= $this->e($user->getLastName()) ?>
    </span>
</div>

<div class="container">
    <ul class="nav nav-tabs nav-justified">
        <li class="active">
            <a data-toggle="tab" href="#home">
                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                 <?= $this->egettext('profile.nav')?>
            </a>
        </li>
        <li>
            <a data-toggle="tab" href="#settings">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                <?= $this->egettext('settings.nav')?>
            </a>
        </li>
        <li>
            <a data-toggle="tab" href="#messages">
                <span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>
                <?= $this->egettext('messages.nav')?>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="home" class="tab-pane fade in active">
            <h3><?= $this->egettext('profile.nav')?></h3>
                <div class="span8">
                <h6>
                    <?= $this->egettext('profile.name')?>: <?= $this->e($user->getFirstName()) ?> <?= $this->e($user->getLastName()) ?>
                </h6>
                <h6>
                    <?= $this->egettext('profile.studentid')?>: s<?= $this->e($user->getStudentId()) ?> 
                </h6>
                <h6>
                    <?= $this->egettext('profile.fieldofstudy')?>: <?= $this->e($discipline ?? $this->gettext('profile.fieldofstudy.discipline.none')) ?> / <?= $this->e($subdiscipline ?? $this->gettext('profile.fieldofstudy.subdiscipline.none')) ?>
                </h6>
                <h6>
                    <?= $this->egettext('profile.tutorialgroup')?>: <?= $this->e($tutorialGroupName ?? $this->gettext('profile.tutorialgroup.none')) ?>
                </h6>
                <h6>
                    <?= $this->egettext('profile.postcount') ?>: <?= $postCount ?>
                </h6>
            </div>
        </div>
        <div id="settings" class="tab-pane fade">
            <h3><?= $this->egettext('settings.nav')?></h3>
            <form>
                <?php
                $this->insert('partials/form/checkbox',
                        [
                    'label'         => 'option.paging.list.label',
                    'name'          => 'option.paging.list',
                    'persistClient' => true
                ])
                ?>
            </form>
        </div>
        <div id="messages" class="tab-pane fade">
            <h3><?= $this->egettext('messages.nav')?></h3>
            <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.</p>
        </div>
    </div>
</div>