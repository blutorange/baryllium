<?php
use Servlet\CheckStudentIdServlet;
require_once '../../private/bootstrap.php';
(new CheckStudentIdServlet())->process();