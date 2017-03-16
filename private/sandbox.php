<?php


/* Use this for quickly testing some php code... */

require_once './bootstrap.php';

//$matches = [];
//$pat = '/hash\s*=\s*["\']([0-9a-f]{16,64})[\'"]/i';
//$stuff = 'qwe qwe hash="80b9f7eee12f785849dd092e81c7d3fF";user="3002591"; qwe asd';
//var_dump(preg_match($pat, $stuff, $matches));
//var_dump($matches);

$l = new Extension\CampusDual\CampusDualLoader("3002591", "11035cre&$!");
$l->getCourse(); 