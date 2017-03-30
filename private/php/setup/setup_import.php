<?php
use Moose\Controller\SetupImportController;
require_once '../../bootstrap.php';
(new SetupImportController())->process();