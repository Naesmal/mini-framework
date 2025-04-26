<h2><?= $title ?></h2>

<div class="auth-form-container">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= $error ?>
        </div>
    <?php endif; ?>
    
    <form action="/login" method="post" class="auth-form">
        <?= Security::generateCSRFToken() ?>
        
        <div class="form-group">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" value="<?= $username ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </div>
        
        <div class="form-footer">
            <p>Vous n'avez pas de compte ? <a href="/register">Inscrivez-vous</a></p>
        </div>
    </form>
</div>