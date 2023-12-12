<?php
require_once 'vendor/autoload.php';

use Guirod\DesignPatterns\Observer\Logger;
use Guirod\DesignPatterns\Observer\OnboardingNotification;
use Guirod\DesignPatterns\Observer\UserRepository;

$repository = new UserRepository();
$repository->attach(new Logger(__DIR__ . "/log.txt"), "*");
$repository->attach(new OnboardingNotification("1@example.com"), "users:created");
$repository->attach(new OnboardingNotification("1@example.com"), "users:updated");

$repository->initialize(__DIR__ . "/users.csv");

$user = $repository->createUser([
    "name" => "John Smith",
    "email" => "john99@example.com",
]);

$repository->updateUser($user, [
    "name" => "John Doe",
    "email" => "john@doe.com",
]);

$repository->deleteUser($user);