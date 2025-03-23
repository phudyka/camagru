<?php 
$pageTitle = 'Login';
require_once ROOT_PATH . '/views/templates/header.php'; 
?>

<div class="form-container fade-in">
    <h2 class="form-title">Login</h2>
    
    <form action="/auth/login" method="POST" id="login-form">
        <div class="mb-3">
            <label for="identifier" class="form-label">Username or Email</label>
            <input type="text" class="form-control" id="identifier" name="identifier" required>
            <div class="form-text text-muted">Enter your username or email address</div>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    
    <div class="mt-3 text-center">
        <p>Don't have an account? <a href="/register">Sign up</a></p>
        <p><a href="/forgot-password">Forgot password?</a></p>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/templates/footer.php'; ?>
