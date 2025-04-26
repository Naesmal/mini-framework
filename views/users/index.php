<h2><?= $title ?></h2>

<div class="action-buttons">
    <?php if (Security::isAuthenticated()): ?>
        <a href="/users/create" class="btn btn-primary">Nouvel utilisateur</a>
    <?php endif; ?>
</div>

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
                    <th>Statut</th>
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
                            <span class="status-badge status-<?= $user['status'] ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="/users/<?= $user['id'] ?>" class="btn btn-sm btn-info" title="Voir">
                                <span class="icon">👁️</span>
                            </a>
                            
                            <?php if (Security::isAuthenticated()): ?>
                                <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-sm btn-warning" title="Modifier">
                                    <span class="icon">✏️</span>
                                </a>
                                
                                <form action="/users/<?= $user['id'] ?>/delete" method="post" class="inline-form">
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