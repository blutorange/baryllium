<?php
use Moose\Controller\SetupImportController;
require_once '../../private/bootstrap.php';
(new SetupImportController())->process();