<?php

namespace Guirod\DesignPatterns\Factory\V2;

class LogisticsFactory implements LogisticsInterface
{
    const TYPE_SEA = "TYPE_SEA";
    const TYPE_ROAD = "TYPE_ROAD";
    public static function createTransport(?string $type = null): LogisticsFactory
    {
        $logistics = null;

        switch($type) {
            case self::TYPE_SEA:
                $logistics = new SeaLogistics();
                break;
            case self::TYPE_ROAD:
                $logistics = new RoadLogistics();
                break;
        }

        return $logistics;
    }
}