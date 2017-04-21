<?php

use Moose\Seed\DormantSeed;
use Moose\Util\DebugUtil;


$a="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEEAAABBCAIAAAABlV4SAAAABnRSTlMAAAAAAABupgeRAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAhUlEQVRoge3ZsRGEMAwAQUwFlEopXyolUAIOgL9hdmMFvlEmj2XOsR+Tk/faftvlzPrCO56moUFDg4YGDQ0aGjQ0aGjQ0KChQUODhgYNDeNfB7wbfWEPGho0NGho0NCgoUFDw5ic8z/9LA0NGho0NGho0NDgztegoUFDg4YGDQ0aGr7QcALQbwsfpmZRCwAAAABJRU5ErkJggg==";
$m=[];
var_dump(preg_match("/^data:image\\/(png|jpg|jpeg|gif);base64,[a-zA-Z0-9+\\/]+={0,3}$/", $a, $m));
var_dump(m);
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