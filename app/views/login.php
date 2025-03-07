<?php 
$pageTitle = 'Connexion';
require_once ROOT_PATH . '/views/templates/header.php'; 
?>

<div class="form-container fade-in">
    <h2 class="form-title">Connexion</h2>
    
    <form action="/auth/login" method="POST" id="login-form">
        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Se souvenir de moi</label>
        </div>
        
        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
    </form>
    
    <div class="mt-3 text-center">
        <p>Vous n'avez pas de compte ? <a href="/register">Inscrivez-vous</a></p>
        <p><a href="/forgot-password">Mot de passe oublié ?</a></p>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/templates/footer.php'; ?>
