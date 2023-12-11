<?php

namespace Guirod\DesignPatterns\Decorator;

class Notifier implements NotifierInterface
{
    // Méthode d'envoi de notification par email
    public function sendNotification():void
    {
        // TODO: Implement sendNotification() method.
        echo "Send Mail<br/>";
    }
}