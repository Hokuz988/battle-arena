<?php
// classes/Naruto.php
require_once 'Character.php';

class Naruto extends Character {
    private $baseStats = [];
    
    public function __construct($name) {
        $this->type = "Naruto";
        $this->maxHp = 1950;
        $this->maxEnergy = 160;
        $this->attack = 75;
        $this->defense = 80;
        $this->speed = 65;
        
        // Guarda stats base para reset
        $this->baseStats = [
            'attack' => $this->attack,
            'defense' => $this->defense,
            'speed' => $this->speed
        ];
        
        parent::__construct($name);
    }
    
    public function getForms() {
        return ['Normal', 'Modo Kurama', 'Sabio dos Seis Caminhos'];
    }
    
    public function getCurrentForm() {
        return $this->currentForm;
    }
    
    public function regenerateEnergy() {
        // Naruto sempre pode regenerar energia
        $regenAmount = 30;
        
        // Bônus adicional na forma Kurama
        if ($this->getCurrentForm() === 'Modo Kurama') {
            $regenAmount += 10; // Total 40
        }
        
        $this->currentEnergy = min($this->maxEnergy, $this->currentEnergy + $regenAmount);
    }
    
    public function canRegenerateEnergy() {
        // Naruto sempre pode regenerar (exceção)
        return true;
    }
    
    public function applyEffects() {
        $damage = 0;
        $messages = [];
        
        // Regeneração de HP na forma Kurama
        if ($this->getCurrentForm() === 'Modo Kurama') {
            $healAmount = 50;
            $actualHeal = min($healAmount, $this->maxHp - $this->currentHp);
            if ($actualHeal > 0) {
                $this->currentHp += $actualHeal;
                $messages[] = "Modo Kurama: Regenerou " . $actualHeal . " de HP";
            }
        }
        
        foreach ($this->effects as $key => $effect) {
            if ($effect['type'] === 'bleed') {
                $bleedDamage = $this->maxHp * 0.05;
                $damage += $bleedDamage;
                $messages[] = "Sangramento: " . round($bleedDamage) . " de dano";
                $this->effects[$key]['duration']--;
                if ($this->effects[$key]['duration'] <= 0) {
                    unset($this->effects[$key]);
                }
            }
            if ($effect['type'] === 'domain') {
                $domainDamage = $this->maxHp * 0.01;
                $damage += $domainDamage;
                $messages[] = "🏯 Expansão de Domínio: " . round($domainDamage) . " de dano";
                $this->effects[$key]['duration']--;
                if ($this->effects[$key]['duration'] <= 0) {
                    unset($this->effects[$key]);
                }
            }
        }
        $this->effects = array_values($this->effects);
        if ($damage > 0) {
            $this->takeDamage($damage);
        }
        return $messages;
    }
    
    public function applyForm($formName) {
        if (!in_array($formName, $this->getForms())) {
            return false;
        }
        
        // Reset stats para base antes de aplicar nova forma
        $this->attack = $this->baseStats['attack'];
        $this->defense = $this->baseStats['defense'];
        $this->speed = $this->baseStats['speed'];
        
        $this->currentForm = $formName;
        $stats = $this->getFormStats($formName);
        
        $this->attack += $stats['atk'];
        $this->defense += $stats['def'];
        $this->speed += $stats['spd'];
        
        return parent::applyForm($formName);
    }
    
    public function getFormStats($formName) {
        $stats = [
            'Normal' => ['atk' => 0, 'def' => 0, 'spd' => 0],
            'Modo Kurama' => ['atk' => 30, 'def' => 25, 'spd' => 15],
            'Sabio dos Seis Caminhos' => ['atk' => 45, 'def' => 30, 'spd' => 25]
        ];
        
        return $stats[$formName] ?? ['atk' => 0, 'def' => 0, 'spd' => 0];
    }
    
    public function getSpecialMoves() {
        return [
            'rasengan' => [
                'name' => 'Rasengan',
                'cost' => 35,
                'desc' => 'Esfera de chakra',
                'animation' => 'wind'
            ],
            'rasenshuriken' => [
                'name' => 'Rasenshuriken',
                'cost' => 55,
                'desc' => 'Shuriken de vento',
                'animation' => 'wind'
            ],
            'kurama' => [
                'name' => 'Kurama',
                'cost' => 75,
                'desc' => 'Poder da Kyuubi',
                'animation' => 'wind'
            ]
        ];
    }
    
    public function execute($target) {
        return $this->attack($target);
    }
    
    public function rasengan($target) {
        if (!$this->useEnergy(35)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚡ ENERGIA INSUFICIENTE! Você precisa de 35 EN para usar Rasengan" . RESET
            ];
        }
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.4 - $target->getDefense() * 0.7;
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
            'animation' => 'wind',
            'message' => "{$this->name} usou Rasengan causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function rasenshuriken($target) {
        if (!$this->useEnergy(55)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚡ ENERGIA INSUFICIENTE! Você precisa de 55 EN para usar Rasenshuriken" . RESET
            ];
        }
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.8 - $target->getDefense() * 0.5;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        // Adiciona variação aleatória ao dano: -7 a +3
        $damage += rand(-7, 3);
        $damage = max(self::MIN_DAMAGE, $damage);
        
        $result = $this->applyDamageToTarget($target, $damage);
        
        // Aplica efeito de veneno mesmo se desviou (habilidade de área)
        if (!$result['evaded']) {
            $target->addEffect(['type' => 'poison', 'duration' => 3, 'icon' => '☠️']);
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
            'animation' => 'wind',
            'message' => "{$this->name} usou Rasenshuriken causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function kurama($target) {
        if (!$this->useEnergy(75)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚡ ENERGIA INSUFICIENTE! Você precisa de 75 EN para usar Kurama" . RESET
            ];
        }
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 2.1 - $target->getDefense() * 0.3;
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
            'animation' => 'wind',
            'message' => "{$this->name} usou Kurama causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function getAnimationType() { return 'wind'; }
}
