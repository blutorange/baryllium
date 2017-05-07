<?php

use Moose\Util\DebugUtil;


$a = ['1'=>'first','2'=>'second','3'=>'third'];
$aa = [1,2,3];
array_map(function($x,$y){
    var_dump($x);
    var_dump($y);
}, $a, \array_keys($a));
die();

var_dump(ord($txt[0]));
var_dump(ord($txt[1]));
echo "asd>>>\xc2\xa0<<<";
var_dump(str_replace("\xc2\xa0", ''));
die();

require_once './bootstrap.php';
Kint::enabled(true);

$d = new DateTime();
$d2 = new DateTime();
var_dump($d->getTimestamp());
var_dump($d2->getTimestamp());

die();

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

DebugUtil::dump(999);

DebugUtil::sendDump();