<?php
// classes/Sukuna.php
require_once 'Character.php';

class Sukuna extends Character {
    private $baseStats = [];
    
    public function __construct($name) {
        $this->type = "Sukuna";
        $this->maxHp = 1800;
        $this->maxEnergy = 120;
        $this->attack = 85;
        $this->defense = 40;
        $this->speed = 70;
        
        // Guarda stats base para reset
        $this->baseStats = [
            'attack' => $this->attack,
            'defense' => $this->defense,
            'speed' => $this->speed
        ];
        
        parent::__construct($name);
    }
    
    public function getBlackFlashChance() {
        // Sukuna tem chance maior de Black Flash
        $streak = $this->blackFlashStreak;
        
        if ($streak >= 3) {
            return 90; // 90% após 3+ acertos
        } elseif ($streak == 2) {
            return 40; // 40% após 2 acertos
        } elseif ($streak == 1) {
            return 20; // 20% após 1 acerto
        } else {
            return 20; // 20% base (nenhum acerto ainda)
        }
    }
    
    public function getForms() {
        return ['Normal', 'HEIAN ERA'];
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
            'HEIAN ERA' => ['atk' => 40, 'def' => 10, 'spd' => 20]
        ];
        
        return $stats[$formName] ?? ['atk' => 0, 'def' => 0, 'spd' => 0];
    }
    
    public function getSpecialMoves() {
        return [
            'cleave' => [
                'name' => 'Cleave',
                'cost' => 30,
                'desc' => 'Corte preciso que sangra',
                'animation' => 'slashing'
            ],
            'dismantle' => [
                'name' => 'Dismantle',
                'cost' => 40,
                'desc' => 'Múltiplos cortes',
                'animation' => 'slashing'
            ],
            'shrine' => [
                'name' => 'Malevolent Shrine',
                'cost' => 80,
                'desc' => 'Expansão de domínio',
                'animation' => 'slashing'
            ]
        ];
    }
    
    public function execute($target) {
        return $this->attack($target);
    }
    
    public function cleave($target) {
        if (!$this->useEnergy(30)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚡ ENERGIA INSUFICIENTE! Você precisa de 30 EN para usar Cleave" . RESET
            ];
        }
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.5 - $target->getDefense() * 0.5;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        // Adiciona variação aleatória ao dano: -7 a +3
        $damage += rand(-7, 3);
        $damage = max(self::MIN_DAMAGE, $damage);
        
        $result = $this->applyDamageToTarget($target, $damage);
        
        // Aplica efeito de sangramento mesmo se desviou (habilidade de área)
        if (!$result['evaded'] && rand(1, 100) <= 20) {
            $target->addEffect([
                'type' => 'bleed',
                'duration' => 3,
                'icon' => '🩸'
            ]);
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
            'animation' => 'slashing',
            'message' => "{$this->name} usou Cleave causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function dismantle($target) {
        if (!$this->useEnergy(40)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚡ ENERGIA INSUFICIENTE! Você precisa de 40 EN para usar Dismantle" . RESET
            ];
        }
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.8 - $target->getDefense() * 0.3;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        // Adiciona variação aleatória ao dano: -7 a +3
        $damage += rand(-7, 3);
        $damage = max(self::MIN_DAMAGE, $damage);
        
        $result = $this->applyDamageToTarget($target, $damage);
        
        // Aplica efeito de sangramento mesmo se desviou (habilidade de área)
        if (!$result['evaded'] && rand(1, 100) <= 50) {
            $target->addEffect([
                'type' => 'bleed',
                'duration' => 3,
                'icon' => '🩸'
            ]);
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
            'animation' => 'slashing',
            'message' => "{$this->name} usou Dismantle causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function shrine($target) {
        if (!$this->useEnergy(80)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚡ ENERGIA INSUFICIENTE! Você precisa de 80 EN para usar Malevolent Shrine" . RESET
            ];
        }
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 2.5 - $target->getDefense() * 0.2;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        // Adiciona variação aleatória ao dano: -7 a +3
        $damage += rand(-7, 3);
        $damage = max(self::MIN_DAMAGE, $damage);
        
        $result = $this->applyDamageToTarget($target, $damage);
        
        // Aplica efeitos de domínio mesmo se desviou (habilidade de área)
        if (!$result['evaded']) {
            // 90% de chance de aplicar todos os efeitos
            if (rand(1, 100) <= 90) {
                $target->addEffect(['type' => 'fear', 'duration' => 2, 'icon' => '😨']);
                $target->addEffect(['type' => 'bleed', 'duration' => 5, 'icon' => '🩸']);
                $target->addEffect(['type' => 'domain', 'duration' => 5, 'icon' => '🏯']);
            }
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
            'animation' => 'slashing',
            'message' => "{$this->name} usou Malevolent Shrine causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function getAnimationType() { return 'slashing'; }
}
?>