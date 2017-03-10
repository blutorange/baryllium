<?php $this->layout('portal') ?>

<form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action) ?>">
    <?php $this->insert('partials/form/input', ['label' => 'User name',
        'name' => 'username', 'required' => true,
        'remote' => '../servlet/CheckUsername.php?username={value}',
        'remoteMessage' => "User name already taken.",
        'placeholder' => 'Any name you like, may include special characters.']) ?>

    <?php $this->insert('partials/form/input', ['label' => 'Password',
        'name' => 'password', 'required' => true, 'mask' => true,
        'minlength' => 5, 'placeholder' => 'At least 5 characters.']) ?>

    <div class="">
        <input id="password" class="btn btn-primary" name="submit" type="submit" />
    </div>    
</form>