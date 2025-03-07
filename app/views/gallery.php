<?php 
$pageTitle = 'Galerie';
require_once ROOT_PATH . '/views/templates/header.php';

// Charger le contrôleur de la galerie
require_once ROOT_PATH . '/controllers/GalleryController.php';
$galleryController = new GalleryController();

// Récupérer le numéro de page depuis l'URL
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // S'assurer que la page est au moins 1

// Nombre d'images par page
$perPage = 12;

// Récupérer les images pour la page actuelle
$images = $galleryController->getImages($page, $perPage);

// Récupérer le nombre total d'images pour la pagination
$totalImages = $galleryController->getTotalImagesCount();
$totalPages = ceil($totalImages / $perPage);
?>

<div class="gallery-header">
    <h1 class="gallery-title">Galerie</h1>
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="/camera" class="btn btn-primary">Prendre une photo</a>
    <?php endif; ?>
</div>

<?php if (empty($images)): ?>
<div class="alert alert-info">
    Aucune image n'a encore été publiée. Soyez le premier à partager une photo !
</div>
<?php else: ?>
<div class="gallery-container">
    <?php foreach ($images as $image): ?>
    <div class="gallery-item fade-in" data-id="<?= $image['id'] ?>">
        <div class="card">
            <div class="card-header">
                <img src="/img/avatars/default.jpg" alt="Avatar" class="avatar">
                <span class="username"><?= htmlspecialchars($image['username']) ?></span>
            </div>
            <img src="/img/uploads/<?= htmlspecialchars($image['filename']) ?>" class="card-img-top" alt="Photo">
            <div class="card-body">
                <div class="image-actions">
                    <button class="like-btn <?= $image['user_liked'] ? 'liked' : '' ?>" data-id="<?= $image['id'] ?>">
                        <i class="fas fa-heart"></i>
                        <span class="likes-count"><?= $image['likes_count'] ?></span>
                    </button>
                    <button class="comment-btn" data-id="<?= $image['id'] ?>">
                        <i class="fas fa-comment"></i>
                        <span class="comments-count"><?= $image['comments_count'] ?></span>
                    </button>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $image['user_id']): ?>
                    <button class="delete-btn" data-id="<?= $image['id'] ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <?php if (!empty($image['description'])): ?>
                <p class="image-description"><?= htmlspecialchars($image['description']) ?></p>
                <?php endif; ?>
                <p class="image-date"><?= date('d/m/Y à H:i', strtotime($image['created_at'])) ?></p>
            </div>
            <div class="card-footer">
                <div class="comment-section" id="comments-<?= $image['id'] ?>">
                    <?php if (!empty($image['comments'])): ?>
                        <?php foreach ($image['comments'] as $comment): ?>
                        <div class="comment">
                            <span class="username"><?= htmlspecialchars($comment['username']) ?></span>
                            <?= htmlspecialchars($comment['comment']) ?>
                            <span class="timestamp"><?= date('d/m/Y', strtotime($comment['created_at'])) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-comments">Aucun commentaire pour le moment.</p>
                    <?php endif; ?>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                <form class="comment-form" data-image-id="<?= $image['id'] ?>">
                    <input type="text" name="comment" placeholder="Ajouter un commentaire..." required>
                    <button type="submit">Publier</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav aria-label="Pagination de la galerie">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Précédent">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        
        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Suivant">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php endif; ?>

<!-- Modal pour afficher les commentaires sur mobile -->
<div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentsModalLabel">Commentaires</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div id="modal-comments-container"></div>
            </div>
            <div class="modal-footer">
                <?php if (isset($_SESSION['user_id'])): ?>
                <form id="modal-comment-form" class="w-100">
                    <div class="input-group">
                        <input type="text" class="form-control" name="comment" placeholder="Ajouter un commentaire..." required>
                        <button class="btn btn-primary" type="submit">Publier</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer cette image ? Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">Supprimer</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des likes
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', function() {
            <?php if (!isset($_SESSION['user_id'])): ?>
            window.location.href = '/login';
            return;
            <?php endif; ?>
            
            const imageId = this.getAttribute('data-id');
            const likesCount = this.querySelector('.likes-count');
            
            fetch('/gallery/like', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'image_id=' + imageId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.liked) {
                        this.classList.add('liked');
                    } else {
                        this.classList.remove('liked');
                    }
                    likesCount.textContent = data.likes_count;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        });
    });
    
    // Gestion des commentaires sur mobile
    document.querySelectorAll('.comment-btn').forEach(button => {
        button.addEventListener('click', function() {
            const imageId = this.getAttribute('data-id');
            const commentsContainer = document.getElementById('comments-' + imageId);
            
            // Sur mobile, ouvrir le modal
            if (window.innerWidth < 768) {
                const modalCommentsContainer = document.getElementById('modal-comments-container');
                modalCommentsContainer.innerHTML = commentsContainer.innerHTML;
                
                const modalForm = document.getElementById('modal-comment-form');
                modalForm.setAttribute('data-image-id', imageId);
                
                const commentsModal = new bootstrap.Modal(document.getElementById('commentsModal'));
                commentsModal.show();
            }
        });
    });
    
    // Gestion de la soumission des commentaires
    function handleCommentSubmit(form) {
        const imageId = form.getAttribute('data-image-id');
        const commentInput = form.querySelector('input[name="comment"]');
        const comment = commentInput.value.trim();
        
        if (comment) {
            fetch('/gallery/comment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'image_id=' + imageId + '&comment=' + encodeURIComponent(comment)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour l'affichage des commentaires
                    const commentsContainer = document.getElementById('comments-' + imageId);
                    const noComments = commentsContainer.querySelector('.no-comments');
                    if (noComments) {
                        noComments.remove();
                    }
                    
                    const newComment = document.createElement('div');
                    newComment.className = 'comment';
                    newComment.innerHTML = `
                        <span class="username">${data.username}</span>
                        ${data.comment}
                        <span class="timestamp">à l'instant</span>
                    `;
                    commentsContainer.appendChild(newComment);
                    
                    // Mettre à jour le compteur de commentaires
                    const commentsCount = document.querySelector(`.comment-btn[data-id="${imageId}"] .comments-count`);
                    commentsCount.textContent = parseInt(commentsCount.textContent) + 1;
                    
                    // Réinitialiser le formulaire
                    commentInput.value = '';
                    
                    // Si le modal est ouvert, mettre à jour son contenu aussi
                    const modalCommentsContainer = document.getElementById('modal-comments-container');
                    if (modalCommentsContainer.innerHTML !== '') {
                        modalCommentsContainer.innerHTML = commentsContainer.innerHTML;
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        }
    }
    
    // Gestion des formulaires de commentaires dans la galerie
    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleCommentSubmit(this);
        });
    });
    
    // Gestion du formulaire de commentaire dans le modal
    const modalCommentForm = document.getElementById('modal-comment-form');
    if (modalCommentForm) {
        modalCommentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleCommentSubmit(this);
        });
    }
    
    // Gestion de la suppression d'images
    let imageToDelete = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            imageToDelete = this.getAttribute('data-id');
            deleteModal.show();
        });
    });
    
    document.getElementById('confirm-delete').addEventListener('click', function() {
        if (imageToDelete) {
            fetch('/gallery/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'image_id=' + imageToDelete
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Supprimer l'élément de la galerie
                    const galleryItem = document.querySelector(`.gallery-item[data-id="${imageToDelete}"]`);
                    galleryItem.remove();
                    
                    // Fermer le modal
                    deleteModal.hide();
                    
                    // Afficher un message de succès
                    alert('Image supprimée avec succès !');
                    
                    // Recharger la page si la galerie est vide
                    if (document.querySelectorAll('.gallery-item').length === 0) {
                        window.location.reload();
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        }
    });
});
</script>

<?php require_once ROOT_PATH . '/views/templates/footer.php'; ?>
