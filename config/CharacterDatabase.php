<?php

class CharacterDatabase {
    private static $instance = null;
    private $database = null;
    private $jsFilePath;
    
    public function __construct() {
        $this->jsFilePath = __DIR__ . '/../data/characters-db.json';
        $this->loadDatabase();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Carrega o banco de dados do arquivo JavaScript
     */
    private function loadDatabase() {
        if (!file_exists($this->jsFilePath)) {
            throw new Exception("Arquivo de banco de dados não encontrado: " . $this->jsFilePath);
        }
        
        $this->database = json_decode(file_get_contents($this->jsFilePath), true);
    }
    
    /**
     * Obtém todos os personagens disponíveis
     */
    public function getAllCharacters() {
        return $this->database['characters'] ?? [];
    }
    
    /**
     * Obtém um personagem específico por ID
     */
    public function getCharacter($id) {
        return $this->database['characters'][$id] ?? null;
    }
    
    /**
     * Obtém as habilidades de um personagem
     */
    public function getCharacterAbilities($characterId) {
        $character = $this->getCharacter($characterId);
        return $character['abilities'] ?? [];
    }
    
    /**
     * Obtém as formas de um personagem
     */
    public function getCharacterForms($characterId) {
        $character = $this->getCharacter($characterId);
        return $character['forms'] ?? [];
    }
    
    /**
     * Obtém os stats base de um personagem
     */
    public function getCharacterBaseStats($characterId) {
        $character = $this->getCharacter($characterId);
        return $character['baseStats'] ?? [];
    }
    
    /**
     * Calcula stats com base na forma
     */
    public function calculateStats($characterId, $formId = 'normal') {
        $character = $this->getCharacter($characterId);
        if (!$character) return null;
        
        $baseStats = $character['baseStats'];
        $form = $character['forms'][$formId] ?? null;
        
        if (!$form) return $baseStats;
        
        $multipliers = $form['multipliers'];
        
        return [
            'hp' => $baseStats['hp'],
            'energy' => $baseStats['energy'],
            'attack' => round($baseStats['attack'] * $multipliers['attack']),
            'defense' => round($baseStats['defense'] * $multipliers['defense']),
            'speed' => round($baseStats['speed'] * $multipliers['speed']),
            'criticalChance' => $baseStats['criticalChance']
        ];
    }
    
    /**
     * Obtém informações sobre um efeito
     */
    public function getEffect($effectId) {
        return $this->database['effects'][$effectId] ?? null;
    }
    
    /**
     * Obtém todos os efeitos
     */
    public function getAllEffects() {
        return $this->database['effects'] ?? [];
    }
    
    /**
     * Verifica se um personagem pode usar uma habilidade
     */
    public function canUseAbility($characterId, $abilityId, $currentEnergy, $currentHp, $currentForm = 'normal') {
        $character = $this->getCharacter($characterId);
        if (!$character) return false;
        
        $abilities = $character['abilities'];
        if (!isset($abilities[$abilityId])) return false;
        
        $ability = $abilities[$abilityId];
        
        // Verifica energia
        if ($currentEnergy < $ability['cost']) return false;
        
        // Verifica condições especiais
        if (isset($ability['condition']) && $ability['condition'] === 'hp_below_10') {
            $hpPercent = ($currentHp / $character['baseStats']['hp']) * 100;
            return $hpPercent <= 10;
        }
        
        return true;
    }
    
    /**
     * Obtém o custo de uma forma
     */
    public function getFormCost($characterId, $formId) {
        $character = $this->getCharacter($characterId);
        if (!$character) return 0;
        
        $form = $character['forms'][$formId] ?? null;
        return $form['cost'] ?? 0;
    }
    
    /**
     * Obtém o custo de uma habilidade
     */
    public function getAbilityCost($characterId, $abilityId) {
        $character = $this->getCharacter($characterId);
        if (!$character) return 0;
        
        $ability = $character['abilities'][$abilityId] ?? null;
        return $ability['cost'] ?? 0;
    }
    
    /**
     * Obtém metadados do banco
     */
    public function getMetadata() {
        return [
            'version' => '1.0.0',
            'lastUpdated' => '2026-04-02',
            'totalCharacters' => count($this->database['characters']),
            'totalMoves' => array_sum(array_map(function($char) {
                return count($char['abilities']);
            }, $this->database['characters']))
        ];
    }
    
    /**
     * Obtém lista de personagens para menu de seleção
     */
    public function getCharacterList() {
        $characters = [];
        foreach ($this->getAllCharacters() as $id => $data) {
            $characters[$id] = [
                'name' => $data['name'],
                'anime' => $data['anime'],
                'description' => $data['description']
            ];
        }
        return $characters;
    }
    
    /**
     * Obtém habilidades formatadas para menu
     */
    public function getAbilitiesForMenu($characterId) {
        $abilities = [];
        $characterAbilities = $this->getCharacterAbilities($characterId);
        
        foreach ($characterAbilities as $id => $ability) {
            $abilities[$id] = [
                'name' => $ability['name'],
                'cost' => $ability['cost'],
                'description' => $ability['description'],
                'type' => $ability['type']
            ];
        }
        
        return $abilities;
    }
    
    /**
     * Obtém formas formatadas para menu
     */
    public function getFormsForMenu($characterId) {
        $forms = [];
        $characterForms = $this->getCharacterForms($characterId);
        
        foreach ($characterForms as $id => $form) {
            $forms[$id] = [
                'name' => $form['name'],
                'cost' => $form['cost'],
                'multipliers' => $form['multipliers']
            ];
        }
        
        return $forms;
    }
}
