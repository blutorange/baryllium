<?php
use Controller\SetupImportController;
require_once '../../bootstrap.php';
(new SetupImportController())->process();