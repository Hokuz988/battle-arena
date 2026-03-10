<?php
// classes/Goku.php
require_once 'Character.php';

class Goku extends Character {
    public function __construct($name) {
        $this->type = "Goku";
        // HP já era alto; mantido >= 1000
        $this->maxHp = 2000;
        $this->maxEnergy = 150;
        $this->attack = 95;
        $this->defense = 35;
        $this->speed = 85;
        parent::__construct($name);
    }
    
    public function getSpecialMoves() {
        return [
            'kamehameha' => [
                'name' => 'Kamehameha',
                'cost' => 40,
                'desc' => 'Onda de energia',
                'animation' => 'kamehameha'
            ],
            'genkidama' => [
                'name' => 'Genki Dama',
                'cost' => 60,
                'desc' => 'Esfera espiritual',
                'animation' => 'genkidama'
            ],
            'teleport' => [
                'name' => 'Teleporte',
                'cost' => 70,
                'desc' => 'Ataque surpresa',
                'animation' => 'teleport'
            ]
        ];
    }
    
    public function execute($target) {
        return $this->attack($target);
    }
    
    public function kamehameha($target) {
        if (!$this->useEnergy(40)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.6 - $target->getDefense() * 0.6;
        $damage = max(self::MIN_DAMAGE, $damage);
        $target->takeDamage($damage);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            'animation' => 'kamehameha',
            'message' => "{$this->name} usou Kamehameha causando " . round($damage) . " de dano!"
        ];
    }
    
    public function genkidama($target) {
        if (!$this->useEnergy(60)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 2.0 - $target->getDefense() * 0.4;
        $damage = max(self::MIN_DAMAGE, $damage);
        $target->takeDamage($damage);
        $this->currentEnergy = min($this->maxEnergy, $this->currentEnergy + 20);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            'animation' => 'genkidama',
            'message' => "{$this->name} usou Genki Dama causando " . round($damage) . " de dano!"
        ];
    }
    
    public function teleport($target) {
        if (!$this->useEnergy(70)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 2.2 - $target->getDefense() * 0.2;
        $damage = max(self::MIN_DAMAGE, $damage);
        $target->takeDamage($damage);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            'animation' => 'teleport',
            'message' => "{$this->name} usou Teleporte causando " . round($damage) . " de dano!"
        ];
    }
    
    public function getAnimationType() { return 'energy'; }
}
?>