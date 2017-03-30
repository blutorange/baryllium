<?php
    use League\Plates\Template\Template;
    use Ui\Section;
    /* @var $this Template */
    $this->layout('portal', ['title' => 'User profile']);
    $this->setActiveSection(Section::$PROFILE);
?>

<?php
    $avatar = $user->getAvatar();
?>

<div class="container">
    <div class="profile-avatar-area">
        <div class="avatar-img-wrapper">
            <?php if ($avatar !== null): ?>
                <img alt="User profile image." class="avatar-img center-block" src="<?= $this->e($avatar)?>"/>
            <?php else: ?>
                <p class="center-block text-center">No avatar set</p>
            <?php endif; ?>
        </div> 
        <span class="center-block text-center">
            <?= $this->e($user->getFirstName()) ?>, <?= $this->e($user->getLastName()) ?>
        </span>
    </div>
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
            <?php $this->insert('partials/component/tc_user_profile', [
                'user' => $user,
                'postCount' => $postCount
            ]); ?>
        </div>
        <div id="settings" class="tab-pane fade">
            <h3><?= $this->egettext('settings.nav')?></h3>
            <?php $this->insert('partials/component/tc_user_settings') ?>
        </div>
        <div id="messages" class="tab-pane fade">
            <h3><?= $this->egettext('messages.nav')?></h3>
            <p class="moose-white">
                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.
            </p>
        </div>
    </div>
</div>