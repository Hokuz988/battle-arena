<?php
/**
 * CharacterModel - Modelo de Dados de Personagens
 * Interface com o banco de dados JavaScript
 */

class CharacterModel {
    private $database;
    
    public function __construct() {
        $this->database = CharacterDatabase::getInstance();
    }
    
    /**
     * Obtém todos os personagens disponíveis
     */
    public function getAvailableCharacters() {
        return $this->database->getCharacterList();
    }
    
    /**
     * Obtém habilidades de um personagem
     */
    public function getCharacterAbilities($characterType) {
        $character = $this->database->getCharacter(strtolower($characterType));
        return $character['abilities'] ?? [];
    }
    
    /**
     * Obtém formas de um personagem
     */
    public function getCharacterForms($characterType) {
        $character = $this->database->getCharacter(strtolower($characterType));
        return $character['forms'] ?? [];
    }
    
    /**
     * Verifica se personagem pode usar habilidade
     */
    public function canUseAbility($characterType, $abilityId, $currentEnergy, $currentHp, $currentForm) {
        return $this->database->canUseAbility(strtolower($characterType), $abilityId, $currentEnergy, $currentHp, $currentForm);
    }
    
    /**
     * Obtém custo de uma forma
     */
    public function getFormCost($characterType, $formId) {
        return $this->database->getFormCost(strtolower($characterType), $formId);
    }
    
    /**
     * Obtém custo de uma habilidade
     */
    public function getAbilityCost($characterType, $abilityId) {
        return $this->database->getAbilityCost(strtolower($characterType), $abilityId);
    }
    
    /**
     * Cria personagem baseado nos dados do banco
     */
    public function createCharacter($type, $name) {
        $characterData = $this->database->getCharacter($type);
        if (!$characterData) {
            throw new Exception("Personagem não encontrado: $type");
        }
        
        // Usa as classes existentes
        switch($type) {
            case 'sukuna':
                return new Sukuna($name);
            case 'goku':
                return new Goku($name);
            case 'naruto':
                return new Naruto($name);
            case 'ichigo':
                return new Ichigo($name);
            default:
                throw new Exception("Personagem inválido: $type");
        }
    }
    
    /**
     * Calcula stats baseados na forma
     */
    public function calculateStats($characterType, $formId = 'normal') {
        return $this->database->calculateStats(strtolower($characterType), $formId);
    }
    
    /**
     * Obtém informações sobre efeito
     */
    public function getEffectInfo($effectId) {
        return $this->database->getEffect($effectId);
    }
    
    /**
     * Obtém metadados do banco
     */
    public function getDatabaseInfo() {
        return $this->database->getMetadata();
    }
}
