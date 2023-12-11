<?php

namespace Guirod\DesignPatterns\Decorator;

class SlackNotifierDecorator extends NotifierDecorator
{
    public function sendNotification():void
    {
        // On appelle la méthode du parent
        parent::sendNotification();
        // Puis on implément la méthode de notification propre à l'envoi par slack
        // ici ...
        echo "Send Slack<br/>";
    }
}