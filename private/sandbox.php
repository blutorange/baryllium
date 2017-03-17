<?php

use Extension\CampusDual\CampusDualLoader;


/* Use this for quickly testing some php code... */

require_once './bootstrap.php';

class A {
    private $x = 5;
    public function call($m, $val) {
        $m($val);
    }
    public function set($val) {
        
        return $this->call($this::setX, $val);
    }
    public function getX() {
        return $x;
    }
    private static function setX(A $a, $val) {
        $a->x = $val;
    }
}

$a = new A();
$a->set(5);
echo $a->getX();

//CampusDualLoader::perform("3002591", "secretPassword", function(CampusDualLoader $loader) {
////    $start = time();
////    $end = time()+7*24*60*60;
////    var_dump($loader->getTimeTableRaw($start, $end));
////    var_dump($loader->getMetaRaw());
//    var_dump($loader->getStudyGroup());
//});