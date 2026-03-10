<?php
// config/Database.php
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dbPath = __DIR__ . '/../database/database.sqlite';
            $dbDir = dirname($dbPath);
            
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0777, true);
            }
            
            $this->pdo = new PDO("sqlite:" . $dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            $this->createTables();
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function createTables() {
        // Histórico de batalhas
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS battles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                player1_name TEXT NOT NULL,
                player2_name TEXT NOT NULL,
                character1_type TEXT NOT NULL,
                character2_type TEXT NOT NULL,
                winner TEXT NOT NULL,
                turns INTEGER NOT NULL,
                battle_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                battle_log TEXT
            )
        ");

        // Tabela de personagens jogáveis
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS characters (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                type TEXT NOT NULL UNIQUE,              -- 'sukuna', 'goku', etc. (slug)
                name TEXT NOT NULL,                     -- Nome exibido
                hp INTEGER NOT NULL,
                energy INTEGER NOT NULL,
                attack INTEGER NOT NULL,
                defense INTEGER NOT NULL,
                speed INTEGER NOT NULL,
                icon_path TEXT DEFAULT NULL,            -- ícone usado na seleção
                sprite_idle_path TEXT DEFAULT NULL,     -- sprite principal na batalha
                sprite_attack_path TEXT DEFAULT NULL    -- sprite de ataque (opcional)
            )
        ");

        $this->seedCharacters();
    }

    private function seedCharacters() {
        // Só insere se a tabela estiver vazia
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM characters");
        $row = $stmt->fetch();
        if ($row && (int)$row['total'] > 0) {
            return;
        }

        $insert = $this->pdo->prepare("
            INSERT OR IGNORE INTO characters
                (type, name, hp, energy, attack, defense, speed, icon_path, sprite_idle_path, sprite_attack_path)
            VALUES
                (:type, :name, :hp, :energy, :attack, :defense, :speed, :icon, :idle, :attack_sprite)
        ");

        // Os valores de atributos espelham as classes PHP (Character subclasses)
        $data = [
            [
                'type' => 'sukuna',
                'name' => 'Sukuna',
                'hp' => 1800,
                'energy' => 120,
                'attack' => 85,
                'defense' => 40,
                'speed' => 70,
                'icon' => 'assets/icons/sukuna.png',
                'idle' => 'assets/sprites/sukuna_idle.png',
                'attack_sprite' => 'assets/sprites/sukuna_attack.png',
            ],
            [
                'type' => 'goku',
                'name' => 'Goku',
                'hp' => 2000,
                'energy' => 150,
                'attack' => 95,
                'defense' => 35,
                'speed' => 85,
                'icon' => 'assets/icons/goku.png',
                'idle' => 'assets/sprites/goku_idle.png',
                'attack_sprite' => 'assets/sprites/goku_attack.png',
            ],
            [
                'type' => 'naruto',
                'name' => 'Naruto',
                'hp' => 1900,
                'energy' => 200,
                'attack' => 75,
                'defense' => 45,
                'speed' => 80,
                'icon' => 'assets/icons/naruto.png',
                'idle' => 'assets/sprites/naruto_idle.png',
                'attack_sprite' => 'assets/sprites/naruto_attack.png',
            ],
            [
                'type' => 'ichigo',
                'name' => 'Ichigo',
                'hp' => 1700,
                'energy' => 130,
                'attack' => 80,
                'defense' => 50,
                'speed' => 90,
                'icon' => 'assets/icons/ichigo.png',
                'idle' => 'assets/sprites/ichigo_idle.png',
                'attack_sprite' => 'assets/sprites/ichigo_attack.png',
            ],
        ];

        foreach ($data as $c) {
            $insert->execute([
                ':type' => $c['type'],
                ':name' => $c['name'],
                ':hp' => $c['hp'],
                ':energy' => $c['energy'],
                ':attack' => $c['attack'],
                ':defense' => $c['defense'],
                ':speed' => $c['speed'],
                ':icon' => $c['icon'],
                ':idle' => $c['idle'],
                ':attack_sprite' => $c['attack_sprite'],
            ]);
        }
    }
    
    public function saveBattle($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO battles 
                (player1_name, player2_name, character1_type, character2_type, winner, turns, battle_log)
                VALUES (:p1, :p2, :c1, :c2, :winner, :turns, :log)
            ");
            
            return $stmt->execute([
                ':p1' => $data['player1'],
                ':p2' => $data['player2'],
                ':c1' => $data['char1'],
                ':c2' => $data['char2'],
                ':winner' => $data['winner'],
                ':turns' => $data['turns'],
                ':log' => json_encode($data['log'])
            ]);
            
        } catch (PDOException $e) {
            error_log("Error saving battle: " . $e->getMessage());
            return false;
        }
    }
    
    public function getRanking() {
        $stmt = $this->pdo->query("
            SELECT 
                player1_name as player,
                COUNT(*) as battles,
                SUM(CASE WHEN winner = player1_name THEN 1 ELSE 0 END) as wins
            FROM battles 
            GROUP BY player1_name
            UNION ALL
            SELECT 
                player2_name as player,
                COUNT(*) as battles,
                SUM(CASE WHEN winner = player2_name THEN 1 ELSE 0 END) as wins
            FROM battles 
            GROUP BY player2_name
            ORDER BY wins DESC
            LIMIT 10
        ");
        
        return $stmt->fetchAll();
    }

    public function getAllCharacters() {
        $stmt = $this->pdo->query("
            SELECT * FROM characters
            ORDER BY id ASC
        ");
        return $stmt->fetchAll();
    }

    public function getCharacterByType($type) {
        $stmt = $this->pdo->prepare("SELECT * FROM characters WHERE type = :type LIMIT 1");
        $stmt->execute([':type' => $type]);
        return $stmt->fetch();
    }
}
?>