<?php

namespace Guirod\DesignPatterns\Factory\V1;

class SeaLogistics extends LogisticsFactory
{
    public static function createTransport(): LogisticsFactory
    {
        return new SeaLogistics();
    }
}