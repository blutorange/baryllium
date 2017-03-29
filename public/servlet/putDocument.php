<?php
use Servlet\PutDocumentServlet;
require_once '../../private/bootstrap.php';
(new PutDocumentServlet())->process();