<?php

namespace Guirod\DesignPatterns\Observer;

/*
 * La classe User est très simple, car ce n'est pas l'objet de l'étude.
 */
class User
{
    public array $attributes = [];

    public function update($data): void
    {
        $this->attributes = array_merge($this->attributes, $data);
    }
}