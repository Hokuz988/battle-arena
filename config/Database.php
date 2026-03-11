<?php
/**
 * Database.php - Stub mínimo para Battle Arena Terminal
 * Não é necessário para o jogo CLI, apenas para compatibilidade de includes
 */

class Database {
    private static $instance = null;
    
    private function __construct() {
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getRanking() {
        return [];
    }
    
    public function getAllCharacters() {
        return [];
    }
}
