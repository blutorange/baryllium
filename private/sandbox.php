<?php

/* Use this for quickly testing some php code... */

class Ab {
};
class Ba extends Ab {
    public function m($a, array $b, array $c = array()) {
        
    }

    public static function method() {
        
    }

}

$a = new Ab();
$b = new Ba();
echo ($b instanceof Ab) ? 'yes' : 'no';