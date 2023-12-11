<?php

namespace Guirod\DesignPatterns\Decorator;

class SMSNotifierDecorator extends NotifierDecorator
{
    public function sendNotification():void
    {
        // On appelle la méthode du parent
        parent::sendNotification();
        // Puis on implément la méthode de notification propre à l'envoi par sms
        // ici ...
        echo "Send SMS<br/>";
    }
}