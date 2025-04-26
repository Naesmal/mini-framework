<?php
/**
 * Classe Model: Classe de base pour tous les modèles
 * Fournit les fonctionnalités CRUD et de requêtes
 */
class Model {
    protected $table;
    protected $db;
    
    // Parties de requête
    protected $whereConditions = [];
    protected $whereParams = [];
    protected $orderByClause = '';
    protected $limitClause = '';
    protected $offsetClause = '';
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Si le nom de la table n'est pas défini, on le dérive du nom de la classe
        if (!isset($this->table)) {
            // Conversion de CamelCase en snake_case et pluralisation simple
            $className = get_class($this);
            $this->table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
        }
    }
    
    /**
     * Réinitialise les conditions de requête
     */
    protected function resetQuery() {
        $this->whereConditions = [];
        $this->whereParams = [];
        $this->orderByClause = '';
        $this->limitClause = '';
        $this->offsetClause = '';
        return $this;
    }
    
    /**
     * Récupère tous les enregistrements
     * @return array
     */
    public function all() {
        $sql = "SELECT * FROM {$this->table}";
        $sql .= $this->buildQueryClauses();
        $result = $this->db->fetchAll($sql, $this->whereParams);
        $this->resetQuery();
        return $result;
    }
    
    /**
     * Trouve un enregistrement par son ID
     * @param int $id
     * @return array|false
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Ajoute une condition WHERE
     * @param string $column La colonne
     * @param mixed $value La valeur
     * @param string $operator L'opérateur de comparaison
     * @return Model
     */
    public function where($column, $value, $operator = '=') {
        $condition = "{$column} {$operator} ?";
        $this->whereConditions[] = $condition;
        $this->whereParams[] = $value;
        return $this;
    }
    
    /**
     * Ajoute une clause ORDER BY
     * @param string $column La colonne
     * @param string $direction La direction (ASC ou DESC)
     * @return Model
     */
    public function orderBy($column, $direction = 'ASC') {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderByClause = "ORDER BY {$column} {$direction}";
        return $this;
    }
    
    /**
     * Ajoute une clause LIMIT
     * @param int $limit
     * @return Model
     */
    public function limit($limit) {
        $this->limitClause = "LIMIT " . (int)$limit;
        return $this;
    }
    
    /**
     * Ajoute une clause OFFSET
     * @param int $offset
     * @return Model
     */
    public function offset($offset) {
        $this->offsetClause = "OFFSET " . (int)$offset;
        return $this;
    }
    
    /**
     * Construit les clauses de la requête
     * @return string
     */
    protected function buildQueryClauses() {
        $query = '';
        
        // Clause WHERE
        if (!empty($this->whereConditions)) {
            $query .= " WHERE " . implode(' AND ', $this->whereConditions);
        }
        
        // Clauses ORDER BY, LIMIT, OFFSET
        if (!empty($this->orderByClause)) {
            $query .= " {$this->orderByClause}";
        }
        
        if (!empty($this->limitClause)) {
            $query .= " {$this->limitClause}";
        }
        
        if (!empty($this->offsetClause)) {
            $query .= " {$this->offsetClause}";
        }
        
        return $query;
    }
    
    /**
     * Exécute la requête et récupère tous les résultats
     * @return array
     */
    public function fetchAll() {
        return $this->all();
    }
    
    /**
     * Exécute la requête et récupère le premier résultat
     * @return array|false
     */
    public function fetchOne() {
        $sql = "SELECT * FROM {$this->table}";
        $sql .= $this->buildQueryClauses();
        $result = $this->db->fetchOne($sql, $this->whereParams);
        $this->resetQuery();
        return $result;
    }
    
    /**
     * Compte le nombre d'enregistrements correspondant aux critères
     * @return int
     */
    public function count() {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $sql .= $this->buildQueryClauses();
        $result = (int) $this->db->fetchValue($sql, $this->whereParams);
        $this->resetQuery();
        return $result;
    }
    
    /**
     * Crée un nouvel enregistrement
     * @param array $data Les données à insérer
     * @return int|false L'ID du nouvel enregistrement ou false
     */
    public function create($data) {
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Met à jour un enregistrement
     * @param int $id L'ID de l'enregistrement
     * @param array $data Les données à mettre à jour
     * @return bool
     */
    public function update($id, $data) {
        return $this->db->update($this->table, $id, $data);
    }
    
    /**
     * Supprime un enregistrement
     * @param int $id L'ID de l'enregistrement
     * @return bool
     */
    public function delete($id) {
        return $this->db->delete($this->table, $id);
    }
}