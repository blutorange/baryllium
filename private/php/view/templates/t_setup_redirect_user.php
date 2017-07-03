<?php
    use League\Plates\Template\Template;
    use Moose\Context\Context;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\Util\CmnCnst;
    /* @var $this Template|PlatesMooseExtension */
    $this->layout('setup', ['title' => 'success']);
    $url = $this->egetResource('private/php/setup/setup_admin.php?' . CmnCnst::URL_PARAM_PRIVATE_KEY . "=$privateKey", Context::PATH_TYPE_LOCAL);
?>

<div class="container" id="t_setup_redirect_user">
    <section class="col-sm-12">
        <h1>
            Configuration file exists now.
        </h1>
        <p class="alert alert-info">
            A new private key was randomly generated. Please store this key somewhere
            safe, such as in a password manager utility. Writing it down is not safe.
            You will need this key every time you startup this application.
        </p>
        <p>
            <code><?=$privateKey?></code>
        </p>
        <p>
            To unlock the application after startup, you need to make a HTTP request
            (GET or POST) to the server from localhost containing the private key.
            For example, the GET request would look like this:
        </p>
        <a href="<?=$url?>"><?=$url?></a>
        <p>
            Incidentally, this is also the link that takes you to the admin account
            setup, so click it!
        </p>
    </section>
</div>