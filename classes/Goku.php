<?php
// classes/Goku.php
require_once 'Character.php';

class Goku extends Character {
    private $baseStats = [];
    
    public function __construct($name) {
        $this->type = "Goku";
        // HP já era alto; mantido >= 1000
        $this->maxHp = 2000;
        $this->maxEnergy = 150;
        $this->attack = 95;
        $this->defense = 35;
        $this->speed = 85;
        
        // Guarda stats base para reset
        $this->baseStats = [
            'attack' => $this->attack,
            'defense' => $this->defense,
            'speed' => $this->speed
        ];
        
        parent::__construct($name);
    }
    
    public function getForms() {
        return ['Normal', 'Super Saiyajin', 'Ultra Instinto', 'Gohan pega o oitão pro pai'];
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
            'Super Saiyajin' => ['atk' => 25, 'def' => 15, 'spd' => 10],
            'Ultra Instinto' => ['atk' => 35, 'def' => 20, 'spd' => 30],
            'Gohan pega o oitão pro pai' => ['atk' => 50, 'def' => 40, 'spd' => 25]
        ];
        
        return $stats[$formName] ?? ['atk' => 0, 'def' => 0, 'spd' => 0];
    }
    
    public function getSpecialMoves() {
        $moves = [
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
        
        // Adiciona ataque especial apenas se estiver na forma "Gohan pega o oitão pro pai"
        if ($this->getCurrentForm() === 'Gohan pega o oitão pro pai') {
            $moves['isso_e_melhor_que_kamehameha'] = [
                'name' => 'Isso é melhor que Kamehameha',
                'cost' => 10,
                'desc' => 'Ataque supremo inspirado no Gohan',
                'animation' => 'isso_e_melhor_que_kamehameha'
            ];
        }
        
        return $moves;
    }
    
    public function execute($target) {
        return $this->attack($target);
    }
    
    public function kamehameha($target) {
        if (!$this->useEnergy(40)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚡ ENERGIA INSUFICIENTE! Você precisa de 40 EN para usar Kamehameha" . RESET
            ];
        }
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.6 - $target->getDefense() * 0.6;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        // Adiciona variação aleatória ao dano: -7 a +3
        $damage += rand(-7, 3);
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
            'animation' => 'kamehameha',
            'message' => "{$this->name} usou Kamehameha causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function genkidama($target) {
        if (!$this->useEnergy(60)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚡ ENERGIA INSUFICIENTE! Você precisa de 60 EN para usar Genki Dama" . RESET
            ];
        }
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 2.0 - $target->getDefense() * 0.4;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        // Adiciona variação aleatória ao dano: -7 a +3
        $damage += rand(-7, 3);
        $damage = max(self::MIN_DAMAGE, $damage);
        
        $result = $this->applyDamageToTarget($target, $damage);
        
        if (!$result['evaded']) {
            $this->currentEnergy = min($this->maxEnergy, $this->currentEnergy + 20);
        }
        
        if ($result['evaded']) {
            return [
                'damage' => 0,
                'animation' => 'dodge',
                'message' => $result['message']
            ];
        }
        
        return [
            'damage' => $result['damage'],
            'animation' => 'genkidama',
            'message' => "{$this->name} usou Genki Dama causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function teleport($target) {
        if (!$this->useEnergy(70)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚡ ENERGIA INSUFICIENTE! Você precisa de 70 EN para usar Teleporte" . RESET
            ];
        }
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 2.2 - $target->getDefense() * 0.2;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        // Adiciona variação aleatória ao dano: -7 a +3
        $damage += rand(-7, 3);
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
            'animation' => 'teleport',
            'message' => "{$this->name} usou Teleporte causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function takeDamage($damage) {
        // Verifica se está em Ultra Instinto e tenta desviar
        if ($this->currentForm === 'Ultra Instinto' && rand(1, 100) <= 60) {
            // 60% de chance de desvio - não toma dano
            return [
                'evaded' => true,
                'damage' => 0,
                'message' => "{$this->name} desviou do ataque com Ultra Instinto!"
            ];
        }
        
        // Dano normal se não desviou
        $damage = max(0, $damage);
        $this->currentHp = max(0, $this->currentHp - $damage);
        $this->stats['damage_taken'] += $damage;
        
        // Retorna formato compatível com outros personagens
        return [
            'evaded' => false,
            'damage' => $damage,
            'message' => null
        ];
    }
    
    public function getAnimationType() { return 'energy'; }
    
    public function isso_e_melhor_que_kamehameha($target) {
        // Verifica se está na forma correta
        if ($this->getCurrentForm() !== 'Gohan pega o oitão pro pai') {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => "⚡ Este ataque só pode ser usado na forma 'Gohan pega o oitão pro pai'!"
            ];
        }
        
        if (!$this->useEnergy(00)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => "⚡ ENERGIA INSUFICIENTE! Você precisa de 100 EN para usar 'Isso é melhor que Kamehameha'"
            ];
        }
        
        $this->stats['specials_used']++;
        
        // Dano massivo inspirado no poder do Gohan
        $damage = $this->attack * 3.5 - $target->getDefense() * 0.2;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        // Adiciona variação aleatória ao dano: -10 a +10
        $damage += rand(-10, 10);
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
            'animation' => 'isso_e_melhor_que_kamehameha',
            'message' => "{$this->name} gritou 'ISSO É MELHOR QUE KAMEHAMEHA!' e deu um tiro, causando " . round($result['damage']) . " de dano MASSIVO!"
        ];
    }
}
?>