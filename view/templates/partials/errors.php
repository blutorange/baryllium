<?php if (isset($errors) && sizeof($errors) > 0): ?>
    <span>The following errors occurred:</span>
    <ul>
        <?php foreach ($errors as $err) : ?>
            <li>
                <?= $this->e($err) ?>
            </li>
        <?php endforeach ?>
    </ul>
<?php endif ?>
