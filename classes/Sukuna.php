<?php
// classes/Sukuna.php
require_once 'Character.php';

class Sukuna extends Character {
    public function __construct($name) {
        $this->type = "Sukuna";
        // HP aumentado para manter mínimo de 1000 (escala antiga x10)
        $this->maxHp = 1800;
        $this->maxEnergy = 120;
        $this->attack = 85;
        $this->defense = 40;
        $this->speed = 70;
        parent::__construct($name);
    }
    
    public function getSpecialMoves() {
        return [
            'cleave' => [
                'name' => 'Cleave',
                'cost' => 30,
                'desc' => 'Corte preciso que sangra',
                // animação específica para a habilidade
                'animation' => 'cleave'
            ],
            'dismantle' => [
                'name' => 'Dismantle',
                'cost' => 40,
                'desc' => 'Múltiplos cortes',
                'animation' => 'dismantle'
            ],
            'shrine' => [
                // tradução correta do ataque
                'name' => 'Santuário Maléfico',
                'cost' => 80,
                'desc' => 'Expansão de domínio',
                'animation' => 'shrine'
            ]
        ];
    }
    
    public function execute($target) {
        return $this->attack($target);
    }
    
    public function cleave($target) {
        if (!$this->useEnergy(30)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.5 - $target->getDefense() * 0.5;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        if (rand(1, 100) <= 30) {
            $target->addEffect([
                'type' => 'bleed',
                'duration' => 3,
                'icon' => '🩸'
            ]);
        }
        
        $target->takeDamage($damage);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            // animação específica da skill
            'animation' => 'cleave',
            'message' => "{$this->name} usou Cleave causando " . round($damage) . " de dano!"
        ];
    }
    
    public function dismantle($target) {
        if (!$this->useEnergy(40)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.8 - $target->getDefense() * 0.3;
        $damage = max(self::MIN_DAMAGE, $damage);
        $target->takeDamage($damage);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            'animation' => 'dismantle',
            'message' => "{$this->name} usou Dismantle causando " . round($damage) . " de dano!"
        ];
    }
    
    public function shrine($target) {
        if (!$this->useEnergy(80)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 2.5 - $target->getDefense() * 0.2;
        $damage = max(self::MIN_DAMAGE, $damage);
        $target->takeDamage($damage);
        $target->addEffect(['type' => 'fear', 'duration' => 2, 'icon' => '😨']);
        $target->addEffect(['type' => 'bleed', 'duration' => 3, 'icon' => '🩸']);
        $this->stats['damage_dealt'] += $damage;
        
        return [
            'damage' => $damage,
            'animation' => 'shrine',
            // nome traduzido no log
            'message' => "{$this->name} usou Santuário Maléfico causando " . round($damage) . " de dano!"
        ];
    }
    
    public function getAnimationType() { return 'slashing'; }
}
?>