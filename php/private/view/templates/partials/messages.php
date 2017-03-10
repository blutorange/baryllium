<?php if (isset($messages) && sizeof($messages) > 0): ?>

    <?php
        $success = array_filter($messages, function($msg){return $msg->isSuccess();}, ARRAY_FILTER_USE_BOTH);
        $info = array_filter($messages, function($msg){return $msg->isInfo();}, ARRAY_FILTER_USE_BOTH);
        $warning = array_filter($messages, function($msg){return $msg->isWarning();}, ARRAY_FILTER_USE_BOTH);
        $danger = array_filter($messages, function($msg){return $msg->isDanger();}, ARRAY_FILTER_USE_BOTH);
    ?>

    <div class="container">

        <?php if (sizeof($success) > 0): ?>
            <div class="alert alert-success">
                <ul>
                    <?php foreach ($succes as $msg) : ?>
                        <?php if ($msg->isSuccess()): ?>
                            <li>
                                <strong><?= $this->e($msg->getMessage()) ?></strong> <?= $this->e($msg->getDetails()) ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (sizeof($info) > 0): ?>
            <div class="alert alert-info">
                <ul>
                    <?php foreach ($info as $msg) : ?>
                        <?php if ($msg->isInfo()): ?>
                            <li>
                                <strong><?= $this->e($msg->getMessage()) ?></strong> <?= $this->e($msg->getDetails()) ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (sizeof($warning) > 0): ?>
            <div class="alert alert-warning">
                <ul>
                    <?php foreach ($warning as $msg) : ?>
                        <?php if ($msg->isWarning()): ?>
                            <li>
                                <strong><?= $this->e($msg->getMessage()) ?></strong> <?= $this->e($msg->getDetails()) ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (sizeof($danger) > 0): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($danger as $msg) : ?>
                        <?php if ($msg->isDanger()): ?>
                            <li>
                                <strong><?= $this->e($msg->getMessage()) ?></strong> <?= $this->e($msg->getDetails()) ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>
        
    </div>

<?php endif ?>