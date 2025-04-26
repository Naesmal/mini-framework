<h2><?= $title ?></h2>

<div class="action-buttons">
    <a href="/users" class="btn btn-secondary">Retour à la liste</a>
    
    <?php if (Security::isAuthenticated()): ?>
        <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-warning">Modifier</a>
        
        <form action="/users/<?= $user['id'] ?>/delete" method="post" class="inline-form">
            <?= Security::generateCSRFToken() ?>
            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                Supprimer
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="user-details">
    <div class="user-header">
        <div class="user-avatar">
            <!-- Utilisation des initiales pour l'avatar -->
            <div class="avatar-placeholder">
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            </div>
        </div>
        <div class="user-title">
            <h3><?= htmlspecialchars($user['username']) ?></h3>
            <span class="status-badge status-<?= $user['status'] ?>">
                <?= ucfirst($user['status']) ?>
            </span>
        </div>
    </div>
    
    <div class="user-info">
        <div class="info-group">
            <label>Email :</label>
            <div><?= htmlspecialchars($user['email']) ?></div>
        </div>
        
        <?php if (isset($user['created_at'])): ?>
            <div class="info-group">
                <label>Date de création :</label>
                <div><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($user['updated_at'])): ?>
            <div class="info-group">
                <label>Dernière mise à jour :</label>
                <div><?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>