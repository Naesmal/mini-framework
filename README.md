# MiniPHP Framework

Un framework PHP léger, intuitif et puissant pour développer rapidement des applications web. Conçu pour être accessible aux débutants tout en offrant les fonctionnalités essentielles pour des applications professionnelles.

## 🚀 Caractéristiques

- **💽 ORM simplifié** - Opérations CRUD intuitives sans SQL complexe
- **🔄 Routage flexible** - Association simple entre URLs et contrôleurs
- **🛡️ Sécurité intégrée** - Protection CSRF, authentification et autorisation
- **📊 Structure MVC** - Organisation claire et séparation des responsabilités
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
git clone https://github.com/Naesmal/miniphp-framework.git

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

Exécutez le script SQL suivant pour initialiser votre base de données :

```sql
-- Créer la base de données
CREATE DATABASE IF NOT EXISTS myapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE myapp;

-- Créer la table des utilisateurs
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Créer un utilisateur par défaut (admin/admin123)
INSERT INTO users (username, email, password, status) VALUES 
('admin', 'admin@example.com', '$2y$10$HR8JGgxHnrBldlwHtX.ot.VQ6X5GNNQDzQ28yrP0v2YO9O3lSLMfq', 'active');
```

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

Ouvrez votre navigateur et naviguez vers l'URL de votre serveur web.

## 📚 Guide d'utilisation

### Structure du projet

La structure du framework suit le pattern MVC (Modèle-Vue-Contrôleur) :

```
mini-framework/
├── config/        # Configuration de l'application
├── core/          # Classes principales du framework
├── controllers/   # Contrôleurs de l'application
├── models/        # Modèles de données
├── views/         # Vues et templates
└── public/        # Point d'entrée public et assets
```

### Créer un modèle

Les modèles représentent les données et la logique métier.

```php
// models/Product.php
class Product extends Model {
    // Nom de la table (optionnel)
    protected $table = 'products';
    
    // Méthodes personnalisées
    public function getAvailableProducts() {
        return $this->where('status', 'available')
                    ->orderBy('name', 'ASC')
                    ->fetchAll();
    }
}
```

### Créer un contrôleur

Les contrôleurs gèrent les requêtes et renvoient les réponses.

```php
// controllers/ProductController.php
class ProductController extends Controller {
    private $productModel;
    
    public function __construct() {
        $this->productModel = new Product();
    }
    
    public function index() {
        $products = $this->productModel->all();
        $this->render('products/index', [
            'title' => 'Nos produits',
            'products' => $products
        ]);
    }
    
    public function show($id) {
        $product = $this->productModel->find($id);
        $this->render('products/show', [
            'title' => 'Détail du produit',
            'product' => $product
        ]);
    }
}
```

### Créer une vue

Les vues sont responsables de l'affichage.

```php
<!-- views/products/index.php -->
<h2><?= $title ?></h2>

<div class="product-grid">
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p><?= htmlspecialchars($product['description']) ?></p>
            <div class="price"><?= number_format($product['price'], 2) ?> €</div>
            <a href="/products/<?= $product['id'] ?>" class="btn btn-primary">Voir détails</a>
        </div>
    <?php endforeach; ?>
</div>
```

### Ajouter des routes

Les routes définissent les URLs de votre application.

```php
// config/routes.php
return [
    // Routes existantes...
    
    // Routes pour les produits
    '/products' => ['ProductController', 'index'],
    '/products/{id}' => ['ProductController', 'show'],
    '/products/create' => ['ProductController', 'create'],
    '/products/{id}/edit' => ['ProductController', 'edit'],
    '/products/{id}/delete' => ['ProductController', 'delete'],
];
```

## 🔍 Exemples CRUD complets

### Récupérer des données

```php
// Tous les enregistrements
$users = (new User())->all();

// Trouver par ID
$user = (new User())->find(1);

// Requêtes complexes
$activeUsers = (new User())
    ->where('status', 'active')
    ->where('created_at', '2023-01-01', '>')
    ->orderBy('username', 'ASC')
    ->limit(10)
    ->fetchAll();

// Compter les enregistrements
$count = (new User())->where('status', 'active')->count();
```

### Créer des données

```php
$userId = (new User())->create([
    'username' => 'johndoe',
    'email' => 'john@example.com',
    'password' => Security::hashPassword('secret123'),
    'status' => 'active'
]);
```

### Mettre à jour des données

```php
$success = (new User())->update(1, [
    'email' => 'newemail@example.com',
    'status' => 'inactive'
]);
```

### Supprimer des données

```php
$success = (new User())->delete(1);
```

## 🔒 Sécurité

### Protection CSRF

```php
<!-- Dans votre formulaire -->
<form action="/users/create" method="post">
    <?= Security::generateCSRFToken() ?>
    <!-- Champs du formulaire -->
</form>

// Dans votre contrôleur
public function create() {
    // Vérifier le jeton CSRF
    Security::checkCSRF();
    
    // Traiter le formulaire...
}
```

### Authentification

```php
// Connexion d'un utilisateur
if (Security::login($username, $password)) {
    // Redirection après connexion réussie
    $this->redirect('/dashboard');
}

// Vérifier si l'utilisateur est connecté
if (Security::isAuthenticated()) {
    // Accès autorisé
} else {
    // Rediriger vers la page de connexion
    $this->redirect('/login');
}

// Déconnexion
Security::logout();
```

### Validation des données

```php
$data = Security::sanitize($_POST);

$errors = [];
if (empty($data['username'])) {
    $errors[] = 'Le nom d\'utilisateur est obligatoire.';
}
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format d\'email invalide.';
}
```

## 🛠️ Personnalisation

### Configuration de l'application

Modifiez le fichier `config/app.php` pour personnaliser les paramètres généraux :

```php
return [
    'dev_mode' => true,        // Mode développement
    'timezone' => 'Europe/Paris', // Fuseau horaire
    'base_url' => 'http://localhost/myapp', // URL de base
    // Autres paramètres...
];
```

### Styles CSS personnalisés

Modifiez le fichier `public/css/style.css` ou ajoutez vos propres feuilles de style.

### Fonctionnalités avancées

Pour ajouter des fonctionnalités avancées, vous pouvez étendre les classes de base :

```php
// core/extentions/CustomModel.php
class CustomModel extends Model {
    // Fonctionnalités supplémentaires...
}
```

## 📝 Bonnes pratiques

1. **Nommage cohérent** - Utilisez des noms descriptifs pour vos classes et méthodes
2. **Validation des données** - Validez toujours les entrées utilisateur
3. **Séparation des responsabilités** - Gardez les modèles, vues et contrôleurs séparés
4. **Commentaires** - Documentez votre code pour faciliter la maintenance
5. **Gestion des erreurs** - Utilisez try/catch pour gérer les exceptions

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à soumettre des pull requests ou à ouvrir des issues pour améliorer ce framework.

## 📜 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 📞 Support

Si vous avez des questions ou des problèmes, veuillez créer une issue dans le dépôt GitHub ou contacter l'auteur directement.

---

Développé avec ❤️ pour simplifier le développement PHP