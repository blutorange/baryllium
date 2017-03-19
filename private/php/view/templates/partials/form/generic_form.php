<form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
    <?php
        foreach ($formFields as $field) {
            echo $field;
        }
    ?>
</form>


