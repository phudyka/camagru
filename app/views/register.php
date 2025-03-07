<?php 
$pageTitle = 'Inscription';
require_once ROOT_PATH . '/views/templates/header.php'; 
?>

<div class="form-container fade-in">
    <h2 class="form-title">Créer un compte</h2>
    
    <form action="/auth/register" method="POST" id="register-form">
        <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" required minlength="3" maxlength="50">
            <div class="form-text text-muted">Entre 3 et 50 caractères</div>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email" class="form-control" id="email" name="email" required>
            <div class="form-text text-muted">Nous ne partagerons jamais votre email</div>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="8">
            <div class="form-text text-muted">Au moins 8 caractères, incluant une majuscule, un chiffre et un caractère spécial</div>
        </div>
        
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="notifications" name="notifications" checked>
            <label class="form-check-label" for="notifications">Recevoir des notifications par email</label>
        </div>
        
        <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
    </form>
    
    <div class="mt-3 text-center">
        <p>Vous avez déjà un compte ? <a href="/login">Connectez-vous</a></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('register-form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    form.addEventListener('submit', function(event) {
        if (password.value !== confirmPassword.value) {
            event.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            return false;
        }
        
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(password.value)) {
            event.preventDefault();
            alert('Le mot de passe doit contenir au moins 8 caractères, incluant une majuscule, un chiffre et un caractère spécial.');
            return false;
        }
    });
});
</script>

<?php require_once ROOT_PATH . '/views/templates/footer.php'; ?>
