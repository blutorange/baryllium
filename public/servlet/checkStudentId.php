<?php
use Moose\Servlet\CheckStudentIdServlet;
require_once '../../private/bootstrap.php';
(new CheckStudentIdServlet())->process();