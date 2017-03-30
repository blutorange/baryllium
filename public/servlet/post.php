<?php
use Moose\Servlet\PostServlet;
require_once '../../private/bootstrap.php';
(new PostServlet())->process();