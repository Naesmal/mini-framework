# MiniPHP Framework

Un framework PHP léger, intuitif et puissant pour développer rapidement des applications web. Conçu pour être accessible aux débutants tout en offrant les fonctionnalités essentielles pour des applications professionnelles.

## 🚀 Caractéristiques

- **💽 ORM simplifié** - Opérations CRUD intuitives sans SQL complexe
- **🔄 Routage flexible** - Association simple entre URLs et contrôleurs
- **🛡️ Sécurité intégrée** - Protection CSRF, authentification et autorisation
- **👥 Gestion des rôles et permissions** - Contrôle d'accès par rôle utilisateur
- **🔐 Authentification avancée** - Sessions sécurisées et connexion persistante (remember me)
- **📊 Structure MVC** - Organisation claire et séparation des responsabilités
- **👑 Panneau d'administration** - Prêt à l'emploi pour gérer utilisateurs et rôles
- **🧩 Design adaptatif** - Interface utilisateur moderne et responsive
- **🔌 Architecture extensible** - Facilement personnalisable et évolutive

## 📋 Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web avec mod_rewrite activé (Apache, Nginx)
- Composer (recommandé mais optionnel)

## ⚡ Installation rapide

### 1. Obtenir le code

```bash
# Cloner le dépôt
git clone https://github.com/Naesmal/mini-framework.git

# Ou télécharger et extraire l'archive
```

### 2. Configuration de la base de données

Modifiez le fichier `config/database.php` avec vos informations de connexion :

```php
return [
    'host' => 'localhost',     // Hôte de la base de données
    'dbname' => 'myapp',       // Nom de la base de données
    'username' => 'root',      // Nom d'utilisateur
    'password' => '',          // Mot de passe
    'charset' => 'utf8mb4'     // Jeu de caractères
];
```

### 3. Créer la base de données

Exécutez le script SQL du fichier `schema.sql` pour initialiser votre base de données avec le support des rôles et permissions.

### 4. Configuration du serveur web

#### Pour Apache :

Créez ou modifiez le fichier `.htaccess` dans le dossier `public/` :

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Pour Nginx :

```nginx
server {
    listen 80;
    server_name example.com;
    root /path/to/miniphp-framework/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Accéder à l'application

Ouvrez votre navigateur et naviguez vers l'URL de votre serveur web. Connectez-vous avec les identifiants administrateur par défaut :

- **Nom d'utilisateur** : admin
- **Mot de passe** : admin123

## 📚 Guide d'utilisation

### Structure du projet

La structure du framework suit le pattern MVC (Modèle-Vue-Contrôleur) :

```
mini-framework/
├── config/        # Configuration de l'application
├── core/          # Classes principales du framework
│   ├── Auth.php     # Authentification et contrôle d'accès
│   ├── Session.php  # Gestion des sessions
│   └── ...
├── controllers/   # Contrôleurs de l'application
├── models/        # Modèles de données
├── views/         # Vues et templates
│   ├── admin/       # Vues du panneau d'administration
│   └── ...
└── public/        # Point d'entrée public et assets
```

## 🔒 Système d'authentification et de contrôle d'accès

Le framework intègre un système complet d'authentification et de contrôle d'accès basé sur les rôles et permissions.

### Gestion des sessions

La classe `Session` gère les sessions utilisateur de manière sécurisée :

```php
// Démarrer ou récupérer une session
Session::start();

// Stocker des données en session
Session::set('key', 'value');

// Récupérer des données de session
$value = Session::get('key', 'default_value');

// Vérifier si une clé existe
if (Session::has('user_id')) {
    // L'utilisateur est connecté
}

// Supprimer une donnée de session
Session::remove('key');

// Messages flash (disponibles uniquement pour la prochaine requête)
Session::setFlash('success', 'Opération réussie!');
$message = Session::getFlash('success');

// Détruire la session
Session::destroy();
```

### Authentification

La classe `Auth` fournit des méthodes pour gérer l'authentification des utilisateurs :

```php
// Connecter un utilisateur
if (Auth::login($username, $password, $remember = false)) {
    // Redirection après connexion réussie
    $this->redirect('/dashboard');
}

// Vérifier si l'utilisateur est connecté
if (Auth::isAuthenticated()) {
    // Accès autorisé
}

// Récupérer l'utilisateur connecté
$user = Auth::currentUser();

// Déconnecter l'utilisateur
Auth::logout();
```

### Contrôle d'accès basé sur les rôles

Le système de contrôle d'accès permet de restreindre l'accès aux fonctionnalités en fonction du rôle de l'utilisateur :

```php
// Vérifier si l'utilisateur a un rôle spécifique
if (Auth::hasRole('admin')) {
    // Afficher les options d'administration
}

// Vérifier si l'utilisateur a une permission spécifique
if (Auth::can('users.edit')) {
    // Afficher le bouton d'édition
}

// Restreindre l'accès à un contrôleur ou une action
class AdminController extends Controller {
    public function __construct() {
        // Restreindre l'accès aux administrateurs
        Auth::restrict('admin', '/login');
    }
}

// Dans une méthode de contrôleur
public function edit($id) {
    // Vérifier si l'utilisateur peut modifier
    Auth::restrict('users.edit', '/error/forbidden');
    
    // Code d'édition...
}
```

## 👑 Création d'un espace d'administration

### 1. Définir les rôles et permissions

Le framework utilise un système de rôles et permissions qui vous permet de définir facilement différents niveaux d'accès :

```sql
-- Ajouter un nouveau rôle
INSERT INTO roles (name, description) VALUES ('editor', 'Éditeur de contenu');

-- Ajouter une permission
INSERT INTO permissions (name, description) VALUES ('content.edit', 'Éditer le contenu');

-- Attribuer des permissions à un rôle
INSERT INTO role_permissions (role_id, permission_id) 
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'editor' AND p.name = 'content.edit';
```

### 2. Créer un contrôleur d'administration

Créez un contrôleur pour gérer votre espace d'administration :

```php
// controllers/MyAdminController.php
class MyAdminController extends Controller {
    public function __construct() {
        // Restreindre l'accès aux rôles spécifiques
        Auth::restrict(['admin', 'editor'], '/login');
    }
    
    public function index() {
        // Afficher le tableau de bord personnalisé
        $this->render('my_admin/dashboard', [
            'title' => 'Mon espace admin'
        ]);
    }
    
    // Autres méthodes...
}
```

### 3. Ajouter les routes

Dans le fichier `config/routes.php`, ajoutez les routes pour votre espace d'administration :

```php
// Routes pour votre espace d'administration
'/my-admin' => ['MyAdminController', 'index'],
'/my-admin/items' => ['MyAdminController', 'items'],
// Autres routes...
```

### 4. Créer les vues

Créez les vues correspondantes dans le dossier `views/my_admin/` :

```
views/
└── my_admin/
    ├── dashboard.php
    ├── items/
    │   ├── index.php
    │   └── edit.php
    └── ...
```

### 5. Vérifier les permissions dans les vues

Dans vos vues, utilisez les méthodes de la classe `Auth` pour afficher ou masquer des éléments selon les permissions :

```php
<!-- Dans une vue -->
<?php if (Auth::can('items.create')): ?>
    <a href="/my-admin/items/create" class="btn btn-primary">Ajouter un élément</a>
<?php endif; ?>
```

## 🔒 Système de rôles prédéfinis

Le framework dispose de trois rôles prédéfinis :

### 1. Admin (Administrateur)
- Accès complet à toutes les fonctionnalités
- Gestion des utilisateurs, rôles et permissions
- Accès au panneau d'administration

### 2. Editor (Éditeur)
- Peut voir et modifier les utilisateurs
- Accès limité au panneau d'administration

### 3. User (Utilisateur)
- Rôle par défaut assigné aux nouveaux utilisateurs
- Peut voir les profils des utilisateurs
- Aucun accès au panneau d'administration

## 🛠️ Personnalisation avancée

### Ajouter des permissions personnalisées

```php
// Dans un modèle personnalisé
class Permission extends Model {
    protected $table = 'permissions';
    
    // Ajouter une nouvelle permission
    public function addPermission($name, $description) {
        return $this->create([
            'name' => $name,
            'description' => $description
        ]);
    }
}

// Utilisation
$permissionModel = new Permission();
$permissionId = $permissionModel->addPermission('articles.publish', 'Publier des articles');
```

### Extensions du système d'authentification

Vous pouvez étendre la classe `Auth` pour ajouter vos propres fonctionnalités :

```php
// core/extensions/MyAuth.php
class MyAuth extends Auth {
    // Méthode pour vérifier si l'utilisateur est l'auteur d'un contenu
    public static function isOwner($contentId, $contentType = 'article') {
        $userId = Session::get('user_id');
        if (!$userId) return false;
        
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) FROM {$contentType}s WHERE id = ? AND user_id = ?";
        return (int) $db->fetchValue($sql, [$contentId, $userId]) > 0;
    }
}

// Utilisation dans un contrôleur
if (MyAuth::isOwner($articleId, 'article') || Auth::hasRole('admin')) {
    // Autoriser la modification
}
```

## 📄 Gestion des sessions utilisateur

La classe `Session` inclut des fonctionnalités pour sécuriser les sessions utilisateur :

1. **Sessions sécurisées** : Cookies HttpOnly, régénération d'ID de session
2. **Protection contre la fixation de session** : Régénération automatique des ID de session
3. **Messages flash** : Idéaux pour les notifications utilisateur
4. **Sessions persistantes** : Option "Se souvenir de moi" avec gestion des jetons

### Exemple d'utilisation des messages flash

```php
// Dans un contrôleur
public function update($id) {
    // Mise à jour...
    if ($success) {
        Session::setFlash('success', 'Mise à jour réussie!');
    } else {
        Session::setFlash('error', 'Erreur lors de la mise à jour.');
    }
    $this->redirect('/items');
}

// Dans la vue
<?php if ($flash = Session::getFlash('success')): ?>
    <div class="alert alert-success"><?= $flash ?></div>
<?php endif; ?>

<?php if ($flash = Session::getFlash('error')): ?>
    <div class="alert alert-danger"><?= $flash ?></div>
<?php endif; ?>
```

## 🔍 Exemples CRUD complets avec rôles

### Récupérer des données avec autorisation

```php
// Dans un contrôleur
public function index() {
    $itemModel = new Item();
    
    // Si l'utilisateur est admin, montrer tous les éléments
    if (Auth::hasRole('admin')) {
        $items = $itemModel->all();
    } 
    // Si éditeur, montrer les éléments actifs et ceux créés par l'utilisateur
    else if (Auth::hasRole('editor')) {
        $userId = Session::get('user_id');
        $items = $itemModel->where('status', 'active')
                          ->orWhere('user_id', $userId)
                          ->orderBy('created_at', 'DESC')
                          ->fetchAll();
    } 
    // Sinon, montrer uniquement les éléments actifs
    else {
        $items = $itemModel->where('status', 'active')->fetchAll();
    }
    
    $this->render('items/index', [
        'title' => 'Liste des éléments',
        'items' => $items
    ]);
}
```

### Créer des données avec autorisation

```php
public function create() {
    // Vérifier si l'utilisateur peut créer des éléments
    Auth::restrict('items.create', '/error/forbidden');
    
    // Si la requête est de type POST
    if ($this->isPost()) {
        // Vérifier le jeton CSRF
        Security::checkCSRF();
        
        // Récupérer les données du formulaire
        $data = Security::sanitize($this->getPostData());
        
        // Ajouter l'ID de l'utilisateur créateur
        $data['user_id'] = Session::get('user_id');
        
        // Créer l'élément
        $itemModel = new Item();
        $itemId = $itemModel->create($data);
        
        if ($itemId) {
            Session::setFlash('success', 'Élément créé avec succès!');
            $this->redirect('/items');
        } else {
            Session::setFlash('error', 'Erreur lors de la création de l\'élément.');
        }
    }
    
    $this->render('items/create', [
        'title' => 'Créer un élément'
    ]);
}
```

## 📊 Bonnes pratiques

1. **Séparation des responsabilités** - Utilisez les classes `Auth` et `Session` pour gérer l'authentification et les sessions
2. **Vérification CSRF** - Utilisez toujours `Security::checkCSRF()` pour les formulaires
3. **Nettoyage des entrées** - Utilisez `Security::sanitize()` pour nettoyer les données
4. **Contrôle d'accès** - Utilisez `Auth::restrict()` au début de vos méthodes
5. **Messages flash** - Utilisez `Session::setFlash()` pour les notifications utilisateur
6. **Helpers dans les vues** - Utilisez `Auth::can()` et `Auth::hasRole()` dans les vues

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à soumettre des pull requests ou à ouvrir des issues pour améliorer ce framework.

## 📜 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 📞 Support

Si vous avez des questions ou des problèmes, veuillez créer une issue dans le dépôt GitHub ou contacter l'auteur directement.

---

Développé avec ❤️ pour simplifier le développement PHP