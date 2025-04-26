<?php
/**
 * Classe Database: Gère la connexion à la base de données et les requêtes
 */
class Database {
    private static $instance = null;
    private $pdo;

    /**
     * Constructeur privé pour empêcher l'instanciation directe (pattern Singleton)
     */
    private function __construct() {
        try {
            $config = require_once __DIR__ . '/../config/database.php';
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            // Redirection vers une page d'erreur ou affichage d'un message
            die("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }

    /**
     * Récupère l'instance unique de Database
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Prépare et exécute une requête SQL
     * @param string $sql La requête SQL
     * @param array $params Les paramètres de la requête
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Récupère plusieurs lignes de résultat
     * @param string $sql La requête SQL
     * @param array $params Les paramètres de la requête
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Récupère une seule ligne de résultat
     * @param string $sql La requête SQL
     * @param array $params Les paramètres de la requête
     * @return array|false
     */
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Récupère une seule valeur
     * @param string $sql La requête SQL
     * @param array $params Les paramètres de la requête
     * @return mixed
     */
    public function fetchValue($sql, $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }

    /**
     * Insère des données dans une table
     * @param string $table Nom de la table
     * @param array $data Données à insérer (clé = nom de colonne, valeur = valeur)
     * @return int|false L'ID de la ligne insérée ou false en cas d'échec
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        if ($this->query($sql, array_values($data))) {
            return $this->pdo->lastInsertId();
        }
        
        return false;
    }

    /**
     * Met à jour des données dans une table
     * @param string $table Nom de la table
     * @param int $id ID de la ligne à mettre à jour
     * @param array $data Données à mettre à jour
     * @return bool Succès ou échec
     */
    public function update($table, $id, $data) {
        $setClause = '';
        foreach ($data as $column => $value) {
            $setClause .= "{$column} = ?, ";
        }
        $setClause = rtrim($setClause, ', ');
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE id = ?";
        $values = array_values($data);
        $values[] = $id;
        
        return $this->query($sql, $values)->rowCount() > 0;
    }

    /**
     * Supprime une ligne d'une table
     * @param string $table Nom de la table
     * @param int $id ID de la ligne à supprimer
     * @return bool Succès ou échec
     */
    public function delete($table, $id) {
        $sql = "DELETE FROM {$table} WHERE id = ?";
        return $this->query($sql, [$id])->rowCount() > 0;
    }

    /**
     * Compte le nombre de lignes dans une table
     * @param string $table Nom de la table
     * @param string $conditions Conditions WHERE (optionnel)
     * @param array $params Paramètres pour les conditions (optionnel)
     * @return int
     */
    public function count($table, $conditions = '', $params = []) {
        $sql = "SELECT COUNT(*) FROM {$table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE {$conditions}";
        }
        
        return (int) $this->fetchValue($sql, $params);
    }
}