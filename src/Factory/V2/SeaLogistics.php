<?php

namespace Guirod\DesignPatterns\Factory\V2;

class SeaLogistics extends LogisticsFactory
{
    public static function createTransport(?string $type = null): LogisticsFactory
    {
        return new SeaLogistics();
    }
}