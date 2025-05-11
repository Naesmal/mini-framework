<div class="admin-container">
    <h2><?= $title ?></h2>
    
    <div class="admin-toolbar">
        <a href="/admin" class="btn btn-secondary">Retour au tableau de bord</a>
        <a href="/admin/users/create" class="btn btn-primary">Nouvel utilisateur</a>
    </div>
    
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
    
    <?php if (empty($users)): ?>
        <div class="alert alert-info">
            Aucun utilisateur trouvé.
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Date d'inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="role-badge">
                                    <?= ucfirst($user['role_name'] ?? 'user') ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $user['status'] ?>">
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                            <td class="actions">
                                <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-sm btn-warning" title="Modifier">
                                    <span class="icon">✏️</span>
                                </a>
                                
                                <?php if ($user['id'] != Session::get('user_id')): ?>
                                    <form action="/admin/users/<?= $user['id'] ?>/delete" method="post" class="inline-form">
                                        <?= Security::generateCSRFToken() ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                            <span class="icon">🗑️</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>