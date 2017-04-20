<?php

// Deletes all data from the current database and
// fills it with random data.
// The password for all users is "password", but
// this can be changed below.

use Moose\Extension\DiningHall\MensaJohannstadtLoader;
use Moose\Seed\DormantSeed;
use Moose\Util\DebugUtil;

require_once '../../bootstrap.php';
Kint::enabled(true);

DormantSeed::grow([
    'Schema' => [
        'Drop',
        'Update' => [true]
    ],
    'University' => [
        'BaDresden'
    ],
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
echo "Done!";
DebugUtil::sendDump();