-- Créer la base de données
CREATE DATABASE IF NOT EXISTS myapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE myapp;

-- Créer la table des rôles
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  description VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insérer les rôles par défaut
INSERT INTO roles (name, description) VALUES 
('admin', 'Administrateur avec accès complet'),
('editor', 'Éditeur avec accès limité'),
('user', 'Utilisateur standard');

-- Modifier la table des utilisateurs pour inclure le rôle
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id INT NOT NULL DEFAULT 3,  -- 3 = user
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Créer un utilisateur admin par défaut (admin/admin123)
INSERT INTO users (username, email, password, role_id, status) VALUES 
('admin', 'admin@example.com', '$2a$12$5T9zP3NiJqIsNStkL7c75e9s4F7RNoAn264TXHwTnjEKaFwGn/aJa', 1, 'active');

-- Créer la table des jetons de connexion persistante
CREATE TABLE user_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(100) NOT NULL,
  expiry DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Créer la table des permissions
CREATE TABLE permissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  description VARCHAR(255) DEFAULT NULL
);

-- Insérer les permissions par défaut
INSERT INTO permissions (name, description) VALUES 
('users.view', 'Voir les utilisateurs'),
('users.create', 'Créer des utilisateurs'),
('users.edit', 'Modifier des utilisateurs'),
('users.delete', 'Supprimer des utilisateurs'),
('admin.access', 'Accès au panneau d\'administration');

-- Créer la table de relation entre rôles et permissions
CREATE TABLE role_permissions (
  role_id INT NOT NULL,
  permission_id INT NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Assigner les permissions par défaut aux rôles
-- Admin - toutes les permissions
INSERT INTO role_permissions (role_id, permission_id) 
SELECT 1, id FROM permissions;

-- Editor - voir et modifier les utilisateurs
INSERT INTO role_permissions (role_id, permission_id) 
SELECT 2, id FROM permissions WHERE name IN ('users.view', 'users.edit');

-- User - seulement voir les utilisateurs
INSERT INTO role_permissions (role_id, permission_id) 
SELECT 3, id FROM permissions WHERE name IN ('users.view');