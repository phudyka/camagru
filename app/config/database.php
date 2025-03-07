<?php

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $host = 'mysql';
        $db_name = getenv('DB_NAME') ?: 'camagru_db';
        $username = getenv('DB_USER') ?: 'camagru_user';
        $password = getenv('DB_PASSWORD') ?: 'secure_password';
        
        $retry_count = 0;
        $max_retries = 5;
        
        while ($retry_count < $max_retries) {
            try {
                $this->conn = new PDO(
                    "mysql:host=$host;charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                
                $this->conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
                $this->conn->exec("USE `$db_name`");
                
                break;
            } catch(PDOException $e) {
                $retry_count++;
                if ($retry_count >= $max_retries) {
                    echo "Erreur de connexion: " . $e->getMessage();
                    die();
                }
                echo "Tentative de connexion à la base de données... ($retry_count/$max_retries)<br>";
                sleep(2);
            }
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