<?php

use Controller\SetupAdminController;

require_once '../../bootstrap.php';

$file = dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'FIRST_INSTALL';

if (file_exists($file)) {
    (new SetupAdminController())->process();
}
else {
    echo "Create file $file to run the setup guide.";
}