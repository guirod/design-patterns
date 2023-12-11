<?php

namespace Guirod\DesignPatterns\Decorator;

abstract class NotifierDecorator implements NotifierInterface
{
    protected NotifierInterface $component;

    public function __construct(NotifierInterface $component)
    {
        $this->component = $component;
    }

    public function sendNotification():void
    {
        // On appelle la méthode du composant décoré (l'envoi par mail)
        $this->component->sendNotification();
    }
}