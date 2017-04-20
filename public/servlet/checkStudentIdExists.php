<?php
use Moose\Servlet\CheckStudentIdExistsServlet;
require_once '../../private/bootstrap.php';
(new CheckStudentIdExistsServlet())->process();