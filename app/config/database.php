<?php

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $host = 'mysql';
        $db_name = getenv('DB_NAME') ?: 'camagru_db';
        $username = getenv('DB_USER') ?: 'camagru_user';
        $password = getenv('DB_PASSWORD') ?: 'C@m4gru!P@ssw0rd2023';
        
        try {
            $connected = false;
            $attempts = 0;
            $max_attempts = 5;
            
            while (!$connected && $attempts < $max_attempts) {
                try {
                    $attempts++;
                    $this->conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->conn->exec("set names utf8");
                    $connected = true;
                } catch (PDOException $e) {
                    if ($attempts < $max_attempts) {
                        // Log l'erreur au lieu de l'afficher
                        error_log("Tentative de connexion à la base de données... ($attempts/$max_attempts)");
                        error_log("Erreur: " . $e->getMessage());
                        sleep(2);
                    } else {
                        error_log("Échec de connexion à la base de données après $max_attempts tentatives");
                        error_log("Erreur finale: " . $e->getMessage());
                        
                        throw new PDOException("Une erreur est survenue lors de la connexion à la base de données. Veuillez réessayer plus tard.");
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Erreur critique de base de données: " . $e->getMessage());
            
            die("Une erreur est survenue. Veuillez contacter l'administrateur.");
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
} 