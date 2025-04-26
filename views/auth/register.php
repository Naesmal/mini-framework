<h2><?= $title ?></h2>

<div class="auth-form-container">
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="/register" method="post" class="auth-form">
        <?= Security::generateCSRFToken() ?>
        
        <div class="form-group">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" value="<?= $data['username'] ?? '' ?>" required>
            <small>Minimum 3 caractères.</small>
        </div>
        
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" value="<?= $data['email'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
            <small>Minimum 6 caractères.</small>
        </div>
        
        <div class="form-group">
            <label for="password_confirm">Confirmer le mot de passe :</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">S'inscrire</button>
        </div>
        
        <div class="form-footer">
            <p>Vous avez déjà un compte ? <a href="/login">Connectez-vous</a></p>
        </div>
    </form>
</div>