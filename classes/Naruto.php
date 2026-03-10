<?php
// classes/Naruto.php
require_once 'Character.php';

class Naruto extends Character {
    public function __construct($name) {
        $this->type = "Naruto";
        // HP aumentado para manter mínimo de 1000 (escala antiga x10)
        $this->maxHp = 1900;
        $this->maxEnergy = 200;
        $this->attack = 75;
        $this->defense = 45;
        $this->speed = 80;
        parent::__construct($name);
    }
    
    public function getSpecialMoves() {
        return [
            'rasengan' => [
                'name' => 'Rasengan',
                'cost' => 35,
                'desc' => 'Esfera de chakra',
                'animation' => 'rasengan'
            ],
            'rasenshuriken' => [
                'name' => 'Rasenshuriken',
                'cost' => 55,
                'desc' => 'Shuriken de vento',
                'animation' => 'rasenshuriken'
            ],
            'kurama' => [
                'name' => 'Modo Kurama',
                'cost' => 75,
                'desc' => 'Poder da Kyuubi',
                'animation' => 'kurama'
            ]
        ];
    }
    
    public function execute($target) {
        return $this->attack($target);
    }
    
    public function rasengan($target) {
        if (!$this->useEnergy(35)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.4 - $target->getDefense() * 0.7;
        $damage = max(self::MIN_DAMAGE, $damage);
        $target->takeDamage($damage);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            'animation' => 'rasengan',
            'message' => "{$this->name} usou Rasengan causando " . round($damage) . " de dano!"
        ];
    }
    
    public function rasenshuriken($target) {
        if (!$this->useEnergy(55)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.8 - $target->getDefense() * 0.5;
        $damage = max(self::MIN_DAMAGE, $damage);
        $target->takeDamage($damage);
        $target->addEffect(['type' => 'poison', 'duration' => 3, 'icon' => '☠️']);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            'animation' => 'rasenshuriken',
            'message' => "{$this->name} usou Rasenshuriken causando " . round($damage) . " de dano!"
        ];
    }
    
    public function kurama($target) {
        if (!$this->useEnergy(75)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 2.1 - $target->getDefense() * 0.3;
        $damage = max(self::MIN_DAMAGE, $damage);
        $target->takeDamage($damage);
        $this->currentHp = min($this->maxHp, $this->currentHp + 30);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            'animation' => 'kurama',
            'message' => "{$this->name} usou Modo Kurama causando " . round($damage) . " de dano!"
        ];
    }
    
    public function getAnimationType() { return 'wind'; }
}
?>