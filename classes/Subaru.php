<?php
// classes/Subaru.php
require_once 'Character.php';

class Subaru extends Character {
    private $baseStats = [];
    private $returnByDeathCounter = 0;
    
    public function __construct($name) {
        $this->type = "Subaru";
        $this->maxHp = 1400;
        $this->maxEnergy = 140;
        $this->attack = 70;
        $this->defense = 85;
        $this->speed = 100;
        
        // Guarda stats base para reset
        $this->baseStats = [
            'attack' => $this->attack,
            'defense' => $this->defense,
            'speed' => $this->speed
        ];
        
        parent::__construct($name);
    }
    
    public function getForms() {
        return ['Normal', 'Resolvido', 'Loop Master'];
    }
    
    public function getCurrentForm() {
        return parent::getCurrentForm();
    }
    
    public function applyForm($formName) {
        if (!in_array($formName, $this->getForms())) {
            return false;
        }
        
        // Reset stats para base antes de aplicar nova forma
        $this->attack = $this->baseStats['attack'];
        $this->defense = $this->baseStats['defense'];
        $this->speed = $this->baseStats['speed'];
        
        $stats = $this->getFormStats($formName);
        
        $this->attack += $stats['atk'];
        $this->defense += $stats['def'];
        $this->speed += $stats['spd'];
        
        return parent::applyForm($formName);
    }
    
    public function getFormStats($formName) {
        $stats = [
            'Normal' => ['atk' => 0, 'def' => 0, 'spd' => 0],
            'Resolvido' => ['atk' => 15, 'def' => 20, 'spd' => 10],
            'Loop Master' => ['atk' => 25, 'def' => 35, 'spd' => 20]
        ];
        
        return $stats[$formName] ?? ['atk' => 0, 'def' => 0, 'spd' => 0];
    }
    
    public function getReturnByDeathCount() {
        return $this->returnByDeathCounter;
    }
    
    public function recordReturnByDeath() {
        if ($this->returnByDeathCounter < 2) {
            $this->returnByDeathCounter++;
        }
        return $this->returnByDeathCounter <= 2;
    }
    
    public function getSpecialMoves() {
        return [
            'whitewhale' => [
                'name' => 'White Whale Contract',
                'cost' => 50,
                'desc' => 'Invoca o poder do Branco Leviatã'
            ],
            'returnbydeath' => [
                'name' => 'Return by Death',
                'cost' => 90,
                'desc' => 'Volta no tempo anulando dano anterior'
            ],
            'packed_lunch' => [
                'name' => 'Packed Lunch Strike',
                'cost' => 35,
                'desc' => 'Ataque com tudo que tem na mochila'
            ]
        ];
    }
    
    public function execute($target) {
        return $this->attack($target);
    }
    
    public function whitewhale($target) {
        if (!$this->useEnergy(50)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.6 - $target->getDefense() * 0.3;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        $result = $this->applyDamageToTarget($target, $damage);
        
        if ($result['evaded']) {
            return [
                'damage' => 0,
                'animation' => 'dodge',
                'message' => $result['message']
            ];
        }
        
        return [
            'damage' => $result['damage'],
            'animation' => 'energy',
            'message' => "{$this->name} usou White Whale Contract causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function returnbydeath($target) {
        if (!$this->useEnergy(90)) return false;
        $this->stats['specials_used']++;
        
        // Restaura HP baseado na energia usada
        $healing = 250;
        $this->currentHp = min($this->maxHp, $this->currentHp + $healing);
        
        return [
            'damage' => 0,
            'animation' => 'heal',
            'message' => "{$this->name} usou Return by Death e restaurou " . $healing . " de HP!"
        ];
    }
    
    public function packed_lunch($target) {
        if (!$this->useEnergy(35)) return false;
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.3 - $target->getDefense() * 0.4;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        $result = $this->applyDamageToTarget($target, $damage);
        
        // Restora um pouco de energia mesmo se desviou
        $this->currentEnergy = min($this->maxEnergy, $this->currentEnergy + 15);
        
        if ($result['evaded']) {
            return [
                'damage' => 0,
                'animation' => 'dodge',
                'message' => $result['message']
            ];
        }
        
        return [
            'damage' => $result['damage'],
            'animation' => 'energy',
            'message' => "{$this->name} usou Packed Lunch Strike causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function getAnimationType() { return 'energy'; }
}
