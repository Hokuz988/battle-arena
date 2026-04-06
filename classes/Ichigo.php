<?php
// classes/Ichigo.php
require_once 'Character.php';
require_once __DIR__ . '/../config/CharacterDatabase.php';

class Ichigo extends Character {
    private $baseStats = [];
    private $mugetsuUsed = false;
    
    public function __construct($name) {
        // Carrega stats do JSON
        $db = CharacterDatabase::getInstance();
        $stats = $db->getCharacterBaseStats('ichigo');
        
        $this->type = "Ichigo";
        $this->maxHp = $stats['hp'];
        $this->maxEnergy = $stats['energy'];
        $this->attack = $stats['attack'];
        $this->defense = $stats['defense'];
        $this->speed = $stats['speed'];
        
        // Guarda stats base para reset
        $this->baseStats = [
            'attack' => $this->attack,
            'defense' => $this->defense,
            'speed' => $this->speed
        ];
        
        parent::__construct($name);
    }
    
    public function getForms() {
        return ['Normal', 'Bankai', 'Ichigo Arrancar'];
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
            'Bankai' => ['atk' => 35, 'def' => 20, 'spd' => 20],
            'Ichigo Arrancar' => ['atk' => 40, 'def' => 15, 'spd' => 25]
        ];
        
        return $stats[$formName] ?? ['atk' => 0, 'def' => 0, 'spd' => 0];
    }
    
    public function getSpecialMoves() {
        return [
            'getsuga' => [
                'name' => 'Getsuga Tensho',
                'cost' => 40,
                'desc' => 'Lâmina de energia',
                'animation' => 'slashing'
            ],
            'bankai' => [
                'name' => 'Bankai',
                'cost' => 60,
                'desc' => 'Liberação da Zanpakuto',
                'animation' => 'slashing'
            ],
            'hollow' => [
                'name' => 'Máscara Hollow',
                'cost' => 50,
                'desc' => 'Poder hollow',
                'animation' => 'slashing'
            ],
            'mugetsu' => [
                'name' => 'Mugetsu',
                'cost' => 0,
                'desc' => 'Ataque final (disponivel apenas com 10% HP)',
                'animation' => 'slashing'
            ]
        ];
    }
    
    public function canUseMugetsu() {
        // Mugetsu só pode ser usado com 10% ou menos de HP
        $hpPercent = ($this->currentHp / $this->maxHp) * 100;
        return $hpPercent <= 10;
    }
    
    public function hasUsedMugetsu() {
        return $this->mugetsuUsed;
    }
    
    public function mugetsu($target) {
        // Verifica se pode usar Mugetsu
        if (!$this->canUseMugetsu()) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚠️ MUGETSU só pode ser usado com 10% ou menos de HP!" . RESET
            ];
        }
        
        $this->stats['specials_used']++;
        
        // Marca que Mugetsu foi usado
        $this->mugetsuUsed = true;
        
        // Deixa energia em 0
        $this->currentEnergy = 0;
        
        // 10000 de dano
        $damage = 10000;
        
        $result = $this->applyDamageToTarget($target, $damage);
        
        // Deixa Ichigo com 1 HP após o ataque
        $this->currentHp = 1;
        
        if ($result['evaded']) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => $result['message']
            ];
        }
        
        return [
            'damage' => $result['damage'],
            'animation' => 'slashing',
            'message' => "{$this->name} usou MUGETSU causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function execute($target) {
        return $this->attack($target);
    }
    
    public function getsuga($target) {
        // Verifica se Mugetsu já foi usado
        if ($this->mugetsuUsed) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚠️ Após usar MUGETSU, você não pode usar mais habilidades!" . RESET
            ];
        }
        
        if (!$this->useEnergy(40)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚡ ENERGIA INSUFICIENTE! Você precisa de 40 EN para usar Getsuga Tensho" . RESET
            ];
        }
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.7 - $target->getDefense() * 0.5;
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
            'animation' => 'slashing',
            'message' => "{$this->name} usou Getsuga Tensho causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function bankai($target) {
        // Verifica se Mugetsu já foi usado
        if ($this->mugetsuUsed) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚠️ Após usar MUGETSU, você não pode usar mais habilidades!" . RESET
            ];
        }
        
        if (!$this->useEnergy(60)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚡ ENERGIA INSUFICIENTE! Você precisa de 60 EN para usar Bankai" . RESET
            ];
        }
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 2.1 - $target->getDefense() * 0.4;
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
            'animation' => 'slashing',
            'message' => "{$this->name} usou Bankai causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function hollow($target) {
        // Verifica se Mugetsu já foi usado
        if ($this->mugetsuUsed) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚠️ Após usar MUGETSU, você não pode usar mais habilidades!" . RESET
            ];
        }
        
        if (!$this->useEnergy(50)) {
            return [
                'damage' => 0,
                'animation' => 'error',
                'message' => RED . "⚡ ENERGIA INSUFICIENTE! Você precisa de 50 EN para usar Máscara Hollow" . RESET
            ];
        }
        $this->stats['specials_used']++;
        
        $damage = $this->attack * 1.9 - $target->getDefense() * 0.3;
        $damage = max(self::MIN_DAMAGE, $damage);
        
        // Adiciona variação aleatória ao dano: -7 a +3
        $damage += rand(-7, 3);
        $damage = max(self::MIN_DAMAGE, $damage);
        
        $result = $this->applyDamageToTarget($target, $damage);
        
        // Cura mesmo se desviou (habilidade de absorção)
        if (!$result['evaded']) {
            $this->currentHp = min($this->maxHp, $this->currentHp + $result['damage'] * 0.3);
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
            'message' => "{$this->name} usou Máscara Hollow causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    public function getAnimationType() { return 'slashing'; }
}
?>