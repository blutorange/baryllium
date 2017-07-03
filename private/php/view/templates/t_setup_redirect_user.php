<?php
    use League\Plates\Template\Template;
    /* @var $this Template|Moose\PlatesExtension\PlatesMooseExtension */
    $this->layout('setup', ['title' => 'success']);
    $url = $this->egetResource('setup_admin.php?' . Moose\Util\CmnCnst::URL_PARAM_PRIVATE_KEY . "=$privateKey");
?>

<div class="container" id="t_setup_redirect_user">
    <section class="col-sm-12">
        <h1>
            Configuration file exists now.
        </h1>
        <p class="alert alert-danger">
            A new private key was randomly generated. Please store this key somewhere
            safe, such as in a password manager utility. Writing it down is not safe.
            You will need this key every time you startup this application.
        </p>
        <pre><?=$privateKey?></pre>
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