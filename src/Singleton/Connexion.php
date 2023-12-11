<?php

namespace Guirod\DesignPatterns\Singleton;

use PDO;
use PDOException;

class Connexion
{
    const SERVER_NAME = "mysql8";
    const USERNAME = "root";
    const PASSWORD = "p@ssw0rd";
    const DB_NAME = 'mvc_tp';

    private static ?Connexion $instance = NULL;

    private ?PDO $conn = null;

    static public function getInstance(): ?Connexion
    {
        if (self::$instance === NULL) {
            try {
                self::$instance = new Connexion();
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }

        return self::$instance;
    }

    public function getConn(): PDO
    {
        return $this->conn;
    }

    /*
     * Private Constructor
     */
    private function __construct()
    {
        $this->conn = new PDO("mysql:host=". self::SERVER_NAME .";dbname=".self::DB_NAME, self::USERNAME, self::PASSWORD);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Nous aurions simplement pu modifier la visibilité à private ici
     * @return void
     */
    public function __clone() {
        trigger_error('Cloning forbidden.', E_USER_ERROR);
    }

    // Ici, on pousse le concept du singleton jusqu'au bout. Dans un projet où il n'y a pas 50 devs qui travaillent dessus, il ne sera pas forcément utile de surcharger ces magic methods.
    public function __wakeup(): void
    {
        trigger_error('Wakeup forbidden.', E_USER_ERROR);
    }

    public function __unserialize(array $data): void
    {
        trigger_error('Unserialize forbidden.', E_USER_ERROR);
    }
}