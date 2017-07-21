<?php if (isset($messages) && sizeof($messages) > 0): ?>

    <?php
        $success = \array_filter($messages, function($msg){return $msg->isSuccess();}, ARRAY_FILTER_USE_BOTH);
        $info = \array_filter($messages, function($msg){return $msg->isInfo();}, ARRAY_FILTER_USE_BOTH);
        $warning = \array_filter($messages, function($msg){return $msg->isWarning();}, ARRAY_FILTER_USE_BOTH);
        $danger = \array_filter($messages, function($msg){return $msg->isDanger();}, ARRAY_FILTER_USE_BOTH);

    ?>

    <div class="container moose-messages" >
        <?php if (sizeof($success) > 0): ?>
            <div class="alert alert-success">
                <ul>
                    <?php foreach ($success as $msg) : ?>
                        <?php if ($msg->isSuccess()): ?>
                            <li>
                                <span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
                                <strong class="msg-short"><?= $this->e($msg->getMessage()) ?></strong>
                                <?php
                                    $tag = \strpos($msg->getDetails(), "\n") !== false ? 'pre' : 'span';
                                ?>
                                <<?=$tag?> class="msg-details"><?= $this->e($msg->getDetails()) ?></<?=$tag?>>
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
                                <span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
                                <strong class="msg-short"><?= $this->e($msg->getMessage()) ?></strong>
                                <?php
                                    $tag = \strpos($msg->getDetails(), "\n") !== false ? 'pre' : 'span';
                                ?>
                                <<?=$tag?> class="msg-details"><?= $this->e($msg->getDetails()) ?></<?=$tag?>>
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
                                <span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
                                <strong class="msg-short"><?= $this->e($msg->getMessage()) ?></strong>
                                <?php
                                    $tag = \strpos($msg->getDetails(), "\n") !== false ? 'pre' : 'span';
                                ?>
                                <<?=$tag?> class="msg-details"><?= $this->e($msg->getDetails()) ?></<?=$tag?>>
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
                                <span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
                                <strong class="msg-short"><?= $this->e($msg->getMessage()) ?></strong>
                                <?php
                                    $tag = \strpos($msg->getDetails(), "\n") !== false ? 'pre' : 'span';
                                ?>
                                <<?=$tag?> class="msg-details"><?= $this->e($msg->getDetails()) ?></<?=$tag?>>
                            </li>
                        <?php endif; ?>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="container moose-messages" ></div>
<?php endif ?>