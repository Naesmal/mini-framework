<h2><?= $title ?></h2>

<div class="welcome-box">
    <h3><?= $message ?></h3>
    
    <p>Votre mini-framework PHP est prêt à l'emploi. Ce framework léger et intuitif vous permettra de développer rapidement des applications web avec :</p>
    
    <ul>
        <li>Opérations CRUD simplifiées</li>
        <li>Connexion PDO à la base de données</li>
        <li>Routage simple et intuitif</li>
        <li>Sécurité de base intégrée</li>
        <li>Structure MVC légère</li>
    </ul>
    
    <div class="get-started">
        <h4>Pour commencer</h4>
        
        <p>Voici quelques liens utiles pour vous aider à démarrer :</p>
        
        <div class="button-group">
            <a href="/users" class="btn btn-primary">Gérer les utilisateurs</a>
            <?php if (!Security::isAuthenticated()): ?>
                <a href="/register" class="btn btn-secondary">Créer un compte</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="features">
    <div class="feature-box">
        <h3>CRUD Simplifié</h3>
        <p>Créez, lisez, mettez à jour et supprimez des données sans effort grâce aux méthodes prédéfinies dans les modèles.</p>
        <pre><code>$users = (new User())->all();
$user = (new User())->find(1);
$userId = (new User())->create($data);
$success = (new User())->update($id, $data);
$success = (new User())->delete($id);</code></pre>
    </div>
    
    <div class="feature-box">
        <h3>Routage Intuitif</h3>
        <p>Les routes sont définies simplement dans un fichier de configuration :</p>
        <pre><code>'/users' => ['UserController', 'index'],
'/users/{id}' => ['UserController', 'show']</code></pre>
    </div>
    
    <div class="feature-box">
        <h3>Sécurité Intégrée</h3>
        <p>Protection CSRF, hachage des mots de passe et authentification :</p>
        <pre><code>&lt;?= Security::generateCSRFToken() ?&gt;
Security::checkCSRF();
Security::hashPassword($password);
Security::login($username, $password);</code></pre>
    </div>
</div>