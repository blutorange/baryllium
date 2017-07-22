<form class="bootstrap-parsley">
    <div class="alert alert-info">
        <?= $this->gettext('dialog.login.message') ?>
    </div>
    <div class="dialog-login-failure alert alert-danger hidden">
        <?= $this->gettext('dialog.login.failed') ?>
    </div>
    <?=
    $this->insert('partials/component/tc_login', [
        'withLanguageSelector' => false
    ])
    ?>
</form>