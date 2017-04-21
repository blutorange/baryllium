<?php

use Moose\Seed\DormantSeed;
use Moose\Util\DebugUtil;


$r = json_decode('{"a":3, "b":9}');
var_dump($r);
foreach ($r as $a => $b) {
    var_dump($a . "=>" . $b);
}
die();

require_once './bootstrap.php';
Kint::enabled(true);

//DormantSeed::grow([
//    'Schema' => [
//        'Drop',
//        'Update' => [true]
//    ],
//    'University' => [
//        'BaDresden'
//    ],
//    'ScheduledEvent' => [
//        'ExpireTokenPurge',
//        'DiningHallMenuFetch' => [MensaJohannstadtLoader::class],
//        'MailSend'
//    ],
//    'FieldOfStudy:1' => [
//        'Informationstechnologie',
//        'Medieninformatik'
//    ],
//    'TutorialGroup' => [
//        'Deterministic'
//    ],
//    'Course' => [
//        'Deterministic' => [25]
//    ],
//    'FieldOfStudy:2' => [
//        'AddDeterministicCourses' => [1]
//    ],
//    'User' => [
//        'Admin',
//        'Deterministic' => [20, 'password']
//    ],
//    'Thread' => [
//        'Deterministic' => [50]
//    ],
//    'Post' => [
//        'Deterministic' => [100]
//    ]    
//]);

//DormantSeed::grow([
//    'Schema' => [
//        'Drop',
//        'Update' => [true]
//    ],
//    'University' => [
//        'BaDresden'
//    ],
//    'ScheduledEvent' => [
//        'ExpireTokenPurge',
//        'DiningHallMenuFetch' => [MensaJohannstadtLoader::class],
//        'MailSend'
//    ],
//    'FieldOfStudy:1' => [
//        'Informationstechnologie',
//        'Medieninformatik'
//    ],
//    'TutorialGroup' => [
//        'Random'
//    ],
//    'Course' => [
//        'Random' => [25]
//    ],
//    'FieldOfStudy:2' => [
//        'AddRandomCourses' => [1]
//    ],
//    'User' => [
//        'Admin',
//        'Random' => [20, 'password']
//    ],
//    'Thread' => [
//        'Random' => [50]
//    ],
//    'Post' => [
//        'Random' => [100]
//    ]    
//]);

//echo "Done!";

$match = [];
\preg_match('/([a-z_0-9\\\\]+)\\(([^:]*):(\d)+\)/i', 'Moose\Entity\User(PWREC:6)', $match);

DebugUtil::dump($match);

DebugUtil::sendDump();