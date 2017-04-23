<?php

// Deletes all data from the current database and
// fills it with deterministic data (the same every time you run this script).
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
        'Deterministic'
    ],
    'Course' => [
        'Deterministic' => [25]
    ],
    'FieldOfStudy:2' => [
        'AddDeterministicCourses' => [1]
    ],
    'User' => [
        'Admin',
        'Deterministic' => [20, 'password']
    ],
    'Thread' => [
        'Deterministic' => [50]
    ],
    'Post' => [
        'Deterministic' => [100]
    ]    
]);
echo "Done!";
DebugUtil::sendDump();