<?php
require_once 'vendor/autoload.php';

use Guirod\DesignPatterns\Factory\V1\RoadLogistics as RoadLogisticsV1;
use Guirod\DesignPatterns\Factory\V1\SeaLogistics as SeaLogisticsV1;
use Guirod\DesignPatterns\Factory\V2\LogisticsFactory as LogisticsV2;

//Depending on the purpose, create Road or Sea logistics
$logistics = RoadLogisticsV1::createTransport();
$logistics = SeaLogisticsV1::createTransport();

//2nd type de factory
$logistics = LogisticsV2::createTransport(LogisticsV2::TYPE_ROAD);
$logistics = LogisticsV2::createTransport(LogisticsV2::TYPE_SEA);