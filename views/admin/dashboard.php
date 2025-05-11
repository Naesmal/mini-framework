<div class="admin-container">
    <h2><?= $title ?></h2>
    
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['users_count'] ?></div>
            <div class="stat-label">Utilisateurs</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['active_users_count'] ?></div>
            <div class="stat-label">Utilisateurs actifs</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['inactive_users_count'] ?></div>
            <div class="stat-label">Utilisateurs inactifs</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['roles_count'] ?></div>
            <div class="stat-label">Rôles</div>
        </div>
    </div>
    
    <div class="admin-sections">
        <div class="admin-section">
            <h3>Derniers utilisateurs enregistrés</h3>
            <?php if (empty($recentUsers)): ?>
                <p>Aucun utilisateur récent.</p>
            <?php else: ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom d'utilisateur</th>
                                <th>Email</th>
                                <th>Statut</th>
                                <th>Date d'inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $user['status'] ?>">
                                            <?= ucfirst($user['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="admin-actions">
                    <a href="/admin/users" class="btn btn-primary">Voir tous les utilisateurs</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="admin-menu">
        <h3>Menu d'administration</h3>
        <div class="admin-menu-items">
            <a href="/admin" class="admin-menu-item active">
                <span class="icon">📊</span>
                <span class="label">Tableau de bord</span>
            </a>
            <a href="/admin/users" class="admin-menu-item">
                <span class="icon">👥</span>
                <span class="label">Utilisateurs</span>
            </a>
            <a href="/admin/roles" class="admin-menu-item">
                <span class="icon">🔑</span>
                <span class="label">Rôles</span>
            </a>
            <a href="/" class="admin-menu-item">
                <span class="icon">🏠</span>
                <span class="label">Retour au site</span>
            </a>
        </div>
    </div>
</div>