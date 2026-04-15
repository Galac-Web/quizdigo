<?php
namespace Config;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private static ?PDO $db = null;

    // Constructorul e privat → nimeni nu poate crea direct instanța
    private function __construct($host, $dbname, $username, $password)
    {
        try {
            self::$db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Conexiunea la baza de date a eșuat: " . $e->getMessage());
        }
    }

    // Metoda principală pentru acces
    public static function getInstance($host, $dbname, $username, $password): self
    {
        if (self::$instance === null) {
            self::$instance = new self($host, $dbname, $username, $password);
        }

        return self::$instance;
    }

    // Returnează conexiunea PDO
    public static function getDB(): PDO
    {
        if (self::$db === null) {
            throw new \Exception("Baza de date nu este inițializată. Apelează getInstance() mai întâi.");
        }

        return self::$db;
    }

    // Prevenim clonarea
    private function __clone() {}

    // Prevenim unserialize
    public function __wakeup()
    {
        throw new \Exception("Nu poți unserializa un singleton.");
    }
}
