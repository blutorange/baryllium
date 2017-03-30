<?php
use Moose\Servlet\DocumentServlet;
require_once '../../private/bootstrap.php';
error_log(strlen(file_get_contents('php://input')));
(new DocumentServlet())->process();