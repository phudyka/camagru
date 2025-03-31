<?php

class CameraController {
    public function saveImage() {
        if (!isset($_SESSION['user_id'])) {
            return [
                'success' => false,
                'message' => 'You must be logged in to save images.'
            ];
        }
        
        // Vérifier le token CSRF
        if (!isset($_POST['csrf_token']) || !CSRFProtection::verifyToken($_POST['csrf_token'])) {
            return [
                'success' => false,
                'message' => 'Invalid form submission. Please try again.'
            ];
        }
        
        $imageData = isset($_POST['image']) ? $_POST['image'] : '';
        $filter = isset($_POST['filter']) ? $_POST['filter'] : 'none';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        
        // Valider les données
        if (empty($imageData)) {
            return [
                'success' => false,
                'message' => 'No image data provided.'
            ];
        }
        
        // Valider la description (max 255 caractères)
        if (strlen($description) > 255) {
            return [
                'success' => false,
                'message' => 'Description is too long (max 255 characters).'
            ];
        }
        
        // Nettoyer la description
        $description = htmlspecialchars($description);
        
        // Valider les données de l'image
        if (strpos($imageData, 'data:image/') !== 0) {
            return [
                'success' => false,
                'message' => 'Invalid image format.'
            ];
        }
        
        // Extraire les données de l'image
        list($type, $imageData) = explode(';', $imageData);
        list(, $imageData) = explode(',', $imageData);
        $imageData = base64_decode($imageData);
        
        // Vérifier le type d'image
        $allowedTypes = ['data:image/jpeg', 'data:image/png', 'data:image/gif'];
        if (!in_array($type, $allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Only JPEG, PNG and GIF images are allowed.'
            ];
        }
        
        // Vérifier la taille de l'image (max 5MB)
        if (strlen($imageData) > 5 * 1024 * 1024) {
            return [
                'success' => false,
                'message' => 'Image is too large (max 5MB).'
            ];
        }
        
        // Générer un nom de fichier unique
        $filename = uniqid() . '_' . time() . '.png';
        $uploadDir = ROOT_PATH . '/public/img/uploads/';
        
        // Créer le répertoire s'il n'existe pas
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Sauvegarder l'image
        file_put_contents($uploadDir . $filename, $imageData);
        
        // Si un filtre est sélectionné, l'appliquer
        if ($filter !== 'none') {
            $this->applyFilter($uploadDir . $filename, $filter);
        }
        
        // Sauvegarder les informations dans la base de données
        $this->image->user_id = $_SESSION['user_id'];
        $this->image->filename = $filename;
        $this->image->description = $description;
        
        if ($this->image->create()) {
            return [
                'success' => true,
                'message' => 'Image saved successfully!',
                'image_id' => $this->image->id,
                'filename' => $filename
            ];
        } else {
            // Supprimer le fichier si l'enregistrement en base de données a échoué
            unlink($uploadDir . $filename);
            
            return [
                'success' => false,
                'message' => 'Failed to save image. Please try again.'
            ];
        }
    }
} 