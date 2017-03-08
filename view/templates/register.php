<?php $this->layout('portal') ?>

<form method="post" action="<?=$this->e($action)?>">
    <?php $this->insert('partials/errors', ['errors' => $errors]) ?>
    
    <label for="username">User name
        <input id="username" name="username" type="input"/>
    </label>

    <label for="password">Password
        <input id="password" name="password" type="password" />
    </label>

    <input id="password" name="submit" type="submit" />
</form>