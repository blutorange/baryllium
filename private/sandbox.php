<?php

use Extension\CampusDual\CampusDualLoader;


/* Use this for quickly testing some php code... */

require_once './bootstrap.php';

CampusDualLoader::perform("3002591", "secretPassword", function(CampusDualLoader $loader) {
//    $start = time();
//    $end = time()+7*24*60*60;
//    var_dump($loader->getTimeTableRaw($start, $end));
//    var_dump($loader->getMetaRaw());
    var_dump($loader->getStudyGroup());
});