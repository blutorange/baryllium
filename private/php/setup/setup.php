<?php

use Moose\Controller\SetupController;

require_once '../../bootstrap.php';

$file = \dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'FIRST_INSTALL';

if (!\file_exists($file)) {
    echo "Create file $file to run the setup guide.";
}
else {
    (new SetupController())->process();
}