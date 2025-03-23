<?php 
$pageTitle = 'Register';
require_once ROOT_PATH . '/views/templates/header.php'; 
?>

<div class="form-container fade-in">
    <h2 class="form-title">Create an Account</h2>
    
    <form action="/auth/register" method="POST" id="register-form">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required minlength="3" maxlength="50">
            <div class="form-text text-muted">Between 3 and 50 characters</div>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
            <div class="form-text text-muted">We'll never share your email</div>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="8">
            <div class="form-text text-muted">At least 8 characters, including uppercase, number and special character</div>
        </div>
        
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="notifications" name="notifications" checked>
            <label class="form-check-label" for="notifications">Receive email notifications</label>
        </div>
        
        <button type="submit" class="btn btn-primary w-100">Sign Up</button>
    </form>
    
    <div class="mt-3 text-center">
        <p>Already have an account? <a href="/login">Login</a></p>
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
            alert('Passwords do not match.');
            return false;
        }
        
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(password.value)) {
            event.preventDefault();
            alert('Password must contain at least 8 characters, including uppercase, number and special character.');
            return false;
        }
    });
});
</script>

<?php require_once ROOT_PATH . '/views/templates/footer.php'; ?>
