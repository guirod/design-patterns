<?php

namespace Guirod\DesignPatterns\Factory\V2;

interface LogisticsInterface
{
    public static function createTransport(?string $type = null): LogisticsFactory;
}