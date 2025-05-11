<div class="profile-container">
    <h2><?= $title ?></h2>
    
    <div class="action-buttons">
        <a href="/profile" class="btn btn-secondary">Retour au profil</a>
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
        <form action="/profile/edit" method="post">
            <?= Security::generateCSRFToken() ?>
            
            <div class="form-group">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                <small>Minimum 3 caractères.</small>
            </div>
            
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            
            <div class="form-note">
                <p>Pour modifier votre mot de passe, veuillez utiliser <a href="/profile/password">cette page</a>.</p>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>