<?php

use Moose\Seed\DormantSeed;
use Moose\Util\DebugUtil;
use Moose\Util\RandomUtil;

require_once './bootstrap.php';
Kint::enabled(true);

DormantSeed::grow([
    'ScheduledEvent' => [
        'ExpireTokenPurge',
        'DiningHallMenuFetch' => [MensaJohannstadtLoader::class],
        'MailSend'
    ],
    'FieldOfStudy:1' => [
        'Informationstechnologie',
        'Medieninformatik'
    ],
    'TutorialGroup' => [
        'Random'
    ],
    'Course' => [
        'Random' => [25]
    ],
    'FieldOfStudy:2' => [
        'AddRandomCourses' => [1]
    ],
    'User' => [
        'Admin',
        'Random' => [20, 'password']
    ],
    'Thread' => [
        'Random' => [50]
    ],
    'Post' => [
        'Random' => [100]
    ]    
]);

//DormantSeed::grow([
//    'FieldOfStudy' => [
//        'Informationstechnologie',
//        'Medieninformatik'
//    ],
//]);
//
//DormantSeed::grow([
//    'TutorialGroup' => [
//        'Random'
//    ],
//]);
//
//DormantSeed::grow([
//    'Course' => [
//        'Random' => [25]
//    ],
//]);
//
//DormantSeed::grow([
//    'FieldOfStudy' => [
//        'AddRandomCourses' => [1]
//    ],
//]);
//
echo "Done!";

DebugUtil::sendDump();