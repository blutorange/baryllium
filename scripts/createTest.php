<?php
use Entity\UserGroup;
use Entity\User;

require_once "bootstrap.php";

$newUserName = $argv[1];
$newUserGroup = $argv[2];

$newUserPwd = strval(rand(1000, 9999));

$group = new UserGroup();
$group->setName($newUserGroup);

$user = new User();
$user->setUsername($newUserName);
$user->setPwdHash($newUserPwd);

$user->addToGroup($group);

$entityManager->persist($user);
$entityManager->persist($group);
$entityManager->flush();

echo "Created User with ID " . $user->getId() . "\n";