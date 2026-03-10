<?php
// classes/Character.php
require_once __DIR__ . '/../interfaces/ActionInterface.php';

abstract class Character implements ActionInterface {
    protected $name;
    protected $type;
    protected $maxHp;
    protected $currentHp;
    protected $maxEnergy;
    protected $currentEnergy;
    protected $attack;
    protected $defense;
    protected $speed;
    protected $defenseBoost = 0;
    protected $effects = [];
    protected $stats = [
        'damage_dealt' => 0,
        'damage_taken' => 0,
        'specials_used' => 0,
        'critical_hits' => 0
    ];
    
    const MIN_DAMAGE = 1;
    const CRITICAL_MULTIPLIER = 1.5;
    
    public function __construct($name) {
        $this->name = $name;
        $this->currentHp = $this->maxHp;
        $this->currentEnergy = $this->maxEnergy;
    }
    
    // Getters
    public function getName() { return $this->name; }
    public function getType() { return $this->type; }
    public function getCurrentHp() { return $this->currentHp; }
    public function getMaxHp() { return $this->maxHp; }
    public function getCurrentEnergy() { return $this->currentEnergy; }
    public function getMaxEnergy() { return $this->maxEnergy; }
    public function getAttack() { return $this->attack; }
    public function getDefense() { return $this->defense + $this->defenseBoost; }
    public function getSpeed() { return $this->speed; }
    public function getEffects() { return $this->effects; }
    public function getStats() { return $this->stats; }
    public function setCurrentHp($hp) {
        $this->currentHp = $hp;
    }

    public function setCurrentEnergy($energy) {
        $this->currentEnergy = $energy;
    }
    
    // Métodos públicos
    public function takeDamage($damage) {
        $damage = max(0, $damage);
        $this->currentHp = max(0, $this->currentHp - $damage);
        $this->stats['damage_taken'] += $damage;
        return $damage;
    }
    
    public function useEnergy($amount) {
        if ($this->currentEnergy < $amount) {
            return false;
        }
        $this->currentEnergy -= $amount;
        return true;
    }
    
    public function regenerateEnergy() {
        $this->currentEnergy = min($this->maxEnergy, $this->currentEnergy + 10);
    }
    
    public function addEffect($effect) {
        $this->effects[] = $effect;
    }
    
    public function applyDefenseBoost($boost) {
        $this->defenseBoost = $boost;
    }
    
    public function removeDefenseBoost() {
        $this->defenseBoost = 0;
    }
    
    public function attack($target) {
        $damage = $this->attack - $target->getDefense() * 0.7;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        // Chance de crítico
        if (rand(1, 100) <= 15) {
            $damage *= self::CRITICAL_MULTIPLIER;
            $this->stats['critical_hits']++;
        }
        
        $target->takeDamage($damage);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            'animation' => 'attack',
            'message' => "{$this->name} atacou causando " . round($damage) . " de dano!"
        ];
    }
    
    public function defend() {
        $this->applyDefenseBoost($this->defense * 0.5);
        return [
            'animation' => 'defend',
            'message' => "{$this->name} está defendendo!"
        ];
    }
    
    abstract public function getSpecialMoves();
}
?>