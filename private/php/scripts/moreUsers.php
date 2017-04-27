<?php

// Deletes all data from the current database and
// fills it with random data.
// The password for all users is "password", but
// this can be changed below.

use Moose\Extension\DiningHall\MensaJohannstadtLoader;
use Moose\Seed\DormantSeed;
use Moose\Util\DebugUtil;

require_once '../../bootstrap.php';

Kint::enabled(true);

DormantSeed::grow([
    'User' => [
        'Random' => [100, 'password']
    ],
]);
?>
<html>
    <body>
        <span id="done">Done</span>
        <?php DebugUtil::sendDump();?>
    </body>
</html>