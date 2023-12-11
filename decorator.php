<?php
require_once 'vendor/autoload.php';

use Guirod\DesignPatterns\Decorator\Notifier;
use Guirod\DesignPatterns\Decorator\FacebookNotifierDecorator;
use Guirod\DesignPatterns\Decorator\SMSNotifierDecorator;
use Guirod\DesignPatterns\Decorator\SlackNotifierDecorator;

// Création de notre notifier de base
$notifier = new Notifier();

$messengerNotifEnabled = true;
$smsNotifEnabled = true;
$slackEnabled = true;

if ($messengerNotifEnabled) {
    $notifier = new FacebookNotifierDecorator($notifier);
}

if ($smsNotifEnabled) {
    $notifier = new SMSNotifierDecorator($notifier);
}

if ($slackEnabled) {
    $notifier = new SlackNotifierDecorator($notifier);
}

// Envoi des messages
$notifier->sendNotification();
