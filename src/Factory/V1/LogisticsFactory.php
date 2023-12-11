<?php

namespace Guirod\DesignPatterns\Factory\V1;

abstract class LogisticsFactory
{
    // Ici, nous avons 2 possibilités
    // - définir la classe et la méthode "createTransport" abstract. Auquel cas nous déléguons en totalité la création du transport aux sous-classes. Dans ce cas, le paramètre "type" sera inutile
    // - Utiliser un paramètre "type" pour permettre à la fabrique de créer l'objet correspondant.
    abstract public static function createTransport(): LogisticsFactory;

    // Ici, nous aurons les méthodes partagées par les différents moyens de transport.
}