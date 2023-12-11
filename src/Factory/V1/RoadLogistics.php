<?php

namespace Guirod\DesignPatterns\Factory\V1;

class RoadLogistics extends LogisticsFactory
{
    public static function createTransport(): LogisticsFactory
    {
        return new RoadLogistics();
    }
}