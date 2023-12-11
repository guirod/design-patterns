<?php
require_once 'vendor/autoload.php';

use Guirod\DesignPatterns\Singleton\Connexion;

$conn = Connexion::getInstance();
$pdo = $conn->getConn();