<?php

class User {
    private $conn;
    private $table = 'users';
    
    public $id;
    public $username;
    public $email;
    public $password;
    public $verified;
    public $verification_token;
    public $notifications_enabled;
    public $created_at;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    public function create() {
        $this->conn->beginTransaction();
        
        try {
            $query = "INSERT INTO " . $this->table . " 
                      SET username = :username, 
                          email = :email, 
                          password = :password, 
                          verification_token = :verification_token,
                          notifications_enabled = :notifications_enabled";
            
            $stmt = $this->conn->prepare($query);
            
            // Nettoyer et sécuriser les données
            $this->username = htmlspecialchars(strip_tags($this->username));
            $this->email = htmlspecialchars(strip_tags($this->email));
            
            // Hacher le mot de passe
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            
            // Lier les paramètres
            $stmt->bindParam(':username', $this->username);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':verification_token', $this->verification_token);
            $stmt->bindParam(':notifications_enabled', $this->notifications_enabled);
            
            // Exécuter la requête
            $stmt->execute();
            $this->id = $this->conn->lastInsertId();
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Erreur lors de la création de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->verified = $row['verified'];
            $this->verification_token = $row['verification_token'];
            $this->notifications_enabled = $row['notifications_enabled'];
            $this->created_at = $row['created_at'];
            return true;
        }
        
        return false;
    }
    
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->verified = $row['verified'];
            $this->verification_token = $row['verification_token'];
            $this->notifications_enabled = $row['notifications_enabled'];
            $this->created_at = $row['created_at'];
            return true;
        }
        
        return false;
    }
    
    public function verifyAccount($token) {
        $query = "UPDATE " . $this->table . " 
                  SET verified = 1, verification_token = NULL 
                  WHERE verification_token = :token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        
        return $stmt->execute() && $stmt->rowCount() > 0;
    }
    
    public function updateNotificationPreference($enabled) {
        $query = "UPDATE " . $this->table . " 
                  SET notifications_enabled = :enabled 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':enabled', $enabled, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    public function storeRememberToken($token, $expiry) {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS remember_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        $query = "DELETE FROM remember_tokens WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->id);
        $stmt->execute();
        
        $query = "INSERT INTO remember_tokens (user_id, token, expires_at) 
                  VALUES (:user_id, :token, :expires_at)";
        $stmt = $this->conn->prepare($query);
        
        $expires_at = date('Y-m-d H:i:s', $expiry);
        
        $stmt->bindParam(':user_id', $this->id);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires_at', $expires_at);
        
        return $stmt->execute();
    }
    
    public function findByRememberToken($token) {
        $query = "SELECT user_id FROM remember_tokens 
                  WHERE token = :token AND expires_at > NOW() 
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['user_id'] : false;
    }
    
    public function updateRememberToken($oldToken, $newToken, $expiry) {
        $query = "UPDATE remember_tokens 
                  SET token = :new_token, expires_at = :expires_at 
                  WHERE token = :old_token";
        $stmt = $this->conn->prepare($query);
        
        $expires_at = date('Y-m-d H:i:s', $expiry);
        
        $stmt->bindParam(':new_token', $newToken);
        $stmt->bindParam(':expires_at', $expires_at);
        $stmt->bindParam(':old_token', $oldToken);
        
        return $stmt->execute();
    }
    
    public function removeRememberToken($token) {
        $query = "DELETE FROM remember_tokens WHERE token = :token";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        
        return $stmt->execute();
    }
} 