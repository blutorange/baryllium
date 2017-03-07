<?php

require_once "bootstrap.php";

$userRepository = $entityManager->getRepository('Entity\User');
$users = $userRepository->findAll();

foreach ($users as $user) {
    echo "Found user with id " . $user->getId() . " and name " . $user->getUsername() . "\n";
    $groups = $user->getGroups();
    echo "Groups are:\n";
    foreach ($groups as $group) {
        echo "  - " . $group->getName() . "\n"; 
    }
}

