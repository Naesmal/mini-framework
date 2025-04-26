<h2><?= $title ?></h2>

<div class="action-buttons">
    <a href="/users" class="btn btn-secondary">Retour à la liste</a>
    <a href="/users/<?= $user['id'] ?>" class="btn btn-info">Voir détails</a>
</div>

<?php if (isset($errors) && !empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="form-container">
    <form action="/users/<?= $user['id'] ?>/edit" method="post">
        <?= Security::generateCSRFToken() ?>
        
        <div class="form-group">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" value="<?= $user['username'] ?>" required>
            <small>Minimum 3 caractères.</small>
        </div>
        
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" value="<?= $user['email'] ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password">
            <small>Laissez vide pour ne pas changer.</small>
        </div>
        
        <div class="form-group">
            <label for="status">Statut :</label>
            <select id="status" name="status">
                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Actif</option>
                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactif</option>
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
    </form>
</div>