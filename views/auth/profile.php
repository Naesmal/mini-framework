<div class="profile-container">
    <h2><?= $title ?></h2>
    
    <?php if (isset($_SESSION['flash_messages']['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['flash_messages']['success'] ?>
        </div>
        <?php unset($_SESSION['flash_messages']['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_messages']['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['flash_messages']['error'] ?>
        </div>
        <?php unset($_SESSION['flash_messages']['error']); ?>
    <?php endif; ?>
    
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <div class="avatar-placeholder">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
            </div>
            <div class="profile-info">
                <h3><?= htmlspecialchars($user['username']) ?></h3>
                <span class="role-badge">
                    <?= ucfirst($role['name'] ?? 'user') ?>
                </span>
                <span class="status-badge status-<?= $user['status'] ?>">
                    <?= ucfirst($user['status']) ?>
                </span>
            </div>
        </div>
        
        <div class="profile-details">
            <div class="detail-group">
                <span class="detail-label">Email:</span>
                <span class="detail-value"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            
            <div class="detail-group">
                <span class="detail-label">Date d'inscription:</span>
                <span class="detail-value"><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></span>
            </div>
            
            <?php if (isset($user['updated_at'])): ?>
                <div class="detail-group">
                    <span class="detail-label">Dernière mise à jour:</span>
                    <span class="detail-value"><?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="profile-actions">
            <a href="/profile/edit" class="btn btn-primary">Modifier mon profil</a>
            <a href="/profile/password" class="btn btn-secondary">Changer de mot de passe</a>
        </div>
    </div>
    
    <?php if (Auth::hasRole('admin')): ?>
        <div class="admin-link">
            <a href="/admin" class="btn btn-warning">Accéder au panneau d'administration</a>
        </div>
    <?php endif; ?>
</div>