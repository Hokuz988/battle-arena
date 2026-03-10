<?php
// classes/Ichigo.php
require_once 'Character.php';

class Ichigo extends Character {
    public function __construct($name) {
        $this->type = "Ichigo";
        // HP aumentado para manter mínimo de 1000 (escala antiga x10)
        $this->maxHp = 1700;
        $this->maxEnergy = 130;
        $this->attack = 80;
        $this->defense = 50;
        $this->speed = 90;
        parent::__construct($name);
    }
    
    public function getSpecialMoves() {
        return [
            'getsuga' => [
                'name' => 'Getsuga Tensho',
                'cost' => 40,
                'desc' => 'Lâmina de energia',
                'animation' => 'getsuga'
            ],
            'bankai' => [
                'name' => 'Bankai',
                'cost' => 60,
                'desc' => 'Liberação da Zanpakuto',
                'animation' => 'bankai'
            ],
            'hollow' => [
                'name' => 'Máscara Hollow',
                'cost' => 50,
                'desc' => 'Poder hollow',
                'animation' => 'hollow'
            ]
        ];
    }
    
    public function execute($target) {
        return $this->attack($target);
    }
    
    public function getsuga($target) {
        if (!$this->useEnergy(40)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.7 - $target->getDefense() * 0.5;
        $damage = max(self::MIN_DAMAGE, $damage);
        $target->takeDamage($damage);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            'animation' => 'getsuga',
            'message' => "{$this->name} usou Getsuga Tensho causando " . round($damage) . " de dano!"
        ];
    }
    
    public function bankai($target) {
        if (!$this->useEnergy(60)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 2.1 - $target->getDefense() * 0.4;
        $damage = max(self::MIN_DAMAGE, $damage);
        $target->takeDamage($damage);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            'animation' => 'bankai',
            'message' => "{$this->name} usou Bankai causando " . round($damage) . " de dano!"
        ];
    }
    
    public function hollow($target) {
        if (!$this->useEnergy(50)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.9 - $target->getDefense() * 0.3;
        $damage = max(self::MIN_DAMAGE, $damage);
        $target->takeDamage($damage);
        $this->currentHp = min($this->maxHp, $this->currentHp + $damage * 0.3);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            'animation' => 'hollow',
            'message' => "{$this->name} usou Máscara Hollow causando " . round($damage) . " de dano!"
        ];
    }
    
    public function getAnimationType() { return 'slashing'; }
}
?>