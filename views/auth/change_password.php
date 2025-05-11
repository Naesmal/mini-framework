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
        <form action="/profile/password" method="post">
            <?= Security::generateCSRFToken() ?>
            
            <div class="form-group">
                <label for="current_password">Mot de passe actuel :</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">Nouveau mot de passe :</label>
                <input type="password" id="new_password" name="new_password" required>
                <small>Minimum 6 caractères.</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
            </div>
        </form>
    </div>
    
    <div class="password-rules">
        <h4>Règles de sécurité pour les mots de passe :</h4>
        <ul>
            <li>Minimum 6 caractères</li>
            <li>Utilisez une combinaison de lettres, chiffres et caractères spéciaux</li>
            <li>Évitez d'utiliser des informations personnelles identifiables</li>
            <li>Ne réutilisez pas d'anciens mots de passe</li>
        </ul>
    </div>
</div>