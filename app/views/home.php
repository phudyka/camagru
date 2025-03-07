<?php 
$pageTitle = 'Accueil';
require_once ROOT_PATH . '/views/templates/header.php'; 
?>

<div class="jumbotron">
    <h1 class="display-4">Camagru</h1>
    <p class="lead">Prenez des photos, ajoutez des filtres amusants et partagez-les avec vos amis !</p>
    
    <?php if (!isset($_SESSION['user_id'])): ?>
        <hr class="my-4">
        <p>Rejoignez notre communauté dès maintenant !</p>
        <a class="btn btn-primary btn-lg" href="/register" role="button">S'inscrire</a>
        <a class="btn btn-outline-secondary btn-lg" href="/login" role="button">Se connecter</a>
    <?php else: ?>
        <hr class="my-4">
        <p>Prêt à capturer de nouveaux moments ?</p>
        <a class="btn btn-primary btn-lg" href="/camera" role="button">Prendre une photo</a>
        <a class="btn btn-outline-secondary btn-lg" href="/gallery" role="button">Voir la galerie</a>
    <?php endif; ?>
</div>

<div class="row mt-5">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <h5 class="card-title">Prenez des photos</h5>
                <p class="card-text">Utilisez votre webcam pour capturer des moments uniques.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <h5 class="card-title">Ajoutez des filtres</h5>
                <p class="card-text">Personnalisez vos photos avec nos filtres amusants.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <h5 class="card-title">Partagez</h5>
                <p class="card-text">Partagez vos créations et recevez des commentaires.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/templates/footer.php'; ?>
