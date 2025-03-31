<?php

class Image {
    private $conn;
    private $table = 'images';
    
    // Propriétés
    public $id;
    public $user_id;
    public $filename;
    public $description;
    public $created_at;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id = :user_id, 
                      filename = :filename, 
                      description = :description";
        
        $stmt = $this->conn->prepare($query);
        
        $this->description = htmlspecialchars(strip_tags($this->description));
        
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':filename', $this->filename);
        $stmt->bindParam(':description', $this->description);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    public function getAll($limit = 12, $offset = 0) {
        $query = "SELECT i.*, u.username 
                  FROM " . $this->table . " i
                  JOIN users u ON i.user_id = u.id
                  ORDER BY i.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT i.*, u.username 
                  FROM " . $this->table . " i
                  JOIN users u ON i.user_id = u.id
                  WHERE i.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->filename = $row['filename'];
            $this->description = $row['description'];
            $this->created_at = $row['created_at'];
            return $row;
        }
        
        return false;
    }
    
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);
        
        return $stmt->execute();
    }
} 