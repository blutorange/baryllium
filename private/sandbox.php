<?php

use Identicon\Identicon;

/* Use this for quickly testing some php code... */

require_once './bootstrap.php';

$identicon = new Identicon();
$image_data = $identicon->getImageDataUri("wqeqwads",64);
var_dump($image_data);
