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
    protected $currentForm = 'Normal';
    protected $formCost = 0;
    protected $blackFlashStreak = 0;
    protected $isDefending = false;
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
    public function getCurrentForm() { return $this->currentForm; }
    public function getFormCost() { return $this->formCost; }
    public function getBlackFlashStreak() { return $this->blackFlashStreak; }
    
    public function getBlackFlashChance() {
        // A chance aumenta CONFORME os acertos sucessivos
        // Verificamos quantos já acertou para dar a chance atual
        $streak = $this->blackFlashStreak;
        
        if ($streak >= 3) {
            return 75; // 75% após 3+ acertos
        } elseif ($streak == 2) {
            return 50; // 50% após 2 acertos
        } elseif ($streak == 1) {
            return 30; // 30% após 1 acerto
        } else {
            return 10; // 10% base (nenhum acerto ainda)
        }
    }
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
        
        // Retorna formato compatível com o método applyDamageToTarget
        return [
            'evaded' => false,
            'damage' => $damage,
            'message' => null
        ];
    }
    
    public function useEnergy($amount) {
        if ($this->currentEnergy < $amount) {
            return false;
        }
        $this->currentEnergy -= $amount;
        return true;
    }
    
    public function regenerateEnergy() {
        // Só regenera se permitido (forma Normal ou defendendo)
        if (!$this->canRegenerateEnergy()) {
            return;
        }
        
        // Regeneração padrão de 10
        $this->currentEnergy = min($this->maxEnergy, $this->currentEnergy + 10);
    }
    
    public function addEffect($effect) {
        $this->effects[] = $effect;
    }
    
    public function removeEffect($effectType) {
        $this->effects = array_filter($this->effects, function($effect) use ($effectType) {
            return $effect['type'] !== $effectType;
        });
    }
    
    public function hasEffect($effectType) {
        foreach ($this->effects as $effect) {
            if ($effect['type'] === $effectType) {
                return true;
            }
        }
        return false;
    }
    
    public function applyEffects() {
        $damage = 0;
        $messages = [];
        
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
                $domainDamage = $this->maxHp * 0.05;
                $damage += $domainDamage;
                $messages[] = "🏯 Expansão de Domínio: " . round($domainDamage) . " de dano";
                
                $this->effects[$key]['duration']--;
                if ($this->effects[$key]['duration'] <= 0) {
                    unset($this->effects[$key]);
                }
            }
            
            if ($effect['type'] === 'burn') {
                $burnDamage = $this->maxHp * 0.08;
                $damage += $burnDamage;
                $messages[] = "🔥 Queimadura: " . round($burnDamage) . " de dano";
                
                // Toca áudio de burn se for a primeira aplicação
                if ($effect['duration'] == 3) { // Assume que burn começa com 3 turnos
                    // Áudio será tocado quando o efeito for aplicado (em Sukuna.php)
                }
                
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
    
    public function getActiveEffectsDisplay() {
        if (empty($this->effects)) {
            return "";
        }
        
        $effectIcons = [];
        foreach ($this->effects as $effect) {
            if (isset($effect['icon'])) {
                $duration = isset($effect['duration']) ? "({$effect['duration']})" : "";
                $effectIcons[] = $effect['icon'] . $duration;
            }
        }
        
        return !empty($effectIcons) ? "Status: " . implode(" ", $effectIcons) : "";
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
        
        // Adiciona variação aleatória ao dano: -7 a +3
        $damage += rand(-7, 3);
        $damage = max(self::MIN_DAMAGE, $damage); // Garante dano mínimo
        
        // Sistema Black Flash progressivo
        $blackFlashChance = $this->getBlackFlashChance();
        $isBlackFlash = rand(1, 100) <= $blackFlashChance;
        
        if ($isBlackFlash) {
            $damage *= 2.0; // Multiplicador de Black Flash
            $this->stats['critical_hits']++;
            $this->blackFlashStreak++;
            
            // Recompensas do Black Flash
            $this->currentEnergy = min($this->maxEnergy, $this->currentEnergy + 50);
            $this->currentHp = min($this->maxHp, $this->currentHp + 100);
            
            // Exibe ASCII art do Black Flash
            if (class_exists('ASCIIArts')) {
                $blackFlashAscii = ASCIIArts::getASCII('blackflash');
                system('clear');
                echo "\n" . $blackFlashAscii . "\n";
                sleep(2);
                system('clear');
            }
        } else {
            // Reset da sequência se não acertou Black Flash
            $this->blackFlashStreak = 0;
            
            // Crítico normal - chance base por personagem
            $critChance = $this->getCriticalChance();
            if (rand(1, 100) <= $critChance) {
                $damage *= self::CRITICAL_MULTIPLIER;
                $this->stats['critical_hits']++;
            }
        }
        
        // Verifica se o alvo desviou (especialmente para Goku)
        $result = $target->takeDamage($damage);
        
        if ($result['evaded']) {
            // Mostra ASCII art de desvio
            if (class_exists('ASCIIArts')) {
                $dodgeAscii = ASCIIArts::getASCII('dodge');
                system('clear');
                echo "\n" . $dodgeAscii . "\n";
                sleep(2);
                system('clear');
            }
            
            return [
                'damage' => 0,
                'animation' => 'dodge',
                'message' => $result['message']
            ];
        }
        
        $this->stats['damage_dealt'] += $result['damage'];
        
        return [
            'damage' => $result['damage'],
            'animation' => $isBlackFlash ? 'blackflash' : 'attack',
            'message' => ($isBlackFlash ? "⚡ BLACK FLASH! " : "") . "{$this->name} atacou causando " . round($result['damage']) . " de dano!"
        ];
    }
    
    // Método auxiliar para aplicar dano com verificação de desvio
    protected function applyDamageToTarget($target, $damage) {
        // Aplica dano e verifica se desviou
        $result = $target->takeDamage($damage);
        
        // Se desviou, mostra ASCII art
        if ($result['evaded']) {
            if (class_exists('ASCIIArts')) {
                $dodgeAscii = ASCIIArts::getASCII('dodge');
                system('clear');
                echo "\n" . $dodgeAscii . "\n";
                sleep(2);
                system('clear');
            }
            
            return [
                'damage' => 0,
                'evaded' => true,
                'message' => $result['message']
            ];
        }
        
        // Dano normal aplicado
        $this->stats['damage_dealt'] += $result['damage'];
        
        return [
            'damage' => $result['damage'],
            'evaded' => false,
            'message' => null
        ];
    }
    
    public function defend() {
        $this->applyDefenseBoost($this->defense * 0.5);
        $this->setDefending(true);
        return [
            'animation' => 'defend',
            'message' => "{$this->name} está defendendo!"
        ];
    }
    
    public function applyForm($formName) {
        $forms = $this->getForms();
        if (!in_array($formName, $forms)) {
            return false;
        }
        
        $this->currentForm = $formName;
        
        // Define o custo da forma
        $custos = [
            'Sukuna' => ['Normal' => 0, 'HEIAN ERA' => 40],
            'Goku' => ['Normal' => 0, 'Super Saiyajin' => 35, 'Ultra Instinto' => 60, 'Gohan pega o oitão pro pai' => 40],
            'Naruto' => ['Normal' => 0, 'Modo Kurama' => 45, 'Sabio dos Seis Caminhos' => 70],
            'Ichigo' => ['Normal' => 0, 'Bankai' => 50, 'Ichigo Arrancar' => 45]
        ];
        
        $charCosts = $custos[$this->type] ?? [];
        $this->formCost = $charCosts[$formName] ?? 0;
        
        // Aplica bônus da forma
        switch ($formName) {
            case 'Super Saiyajin':
                $this->attack *= 1.5;
                $this->defense *= 1.3;
                $this->speed *= 1.4;
                break;
            case 'Ultra Instinto':
                $this->attack *= 2.0;
                $this->defense *= 1.8;
                $this->speed *= 2.2;
                break;
            case 'Gohan pega o oitão pro pai':
                $this->attack *= 2.3;
                $this->defense *= 2.0;
                $this->speed *= 1.8;
                break;
            case 'Modo Kurama':
                $this->attack *= 1.6;
                $this->defense *= 1.4;
                $this->speed *= 1.3;
                break;
            case 'Sabio dos Seis Caminhos':
                $this->attack *= 2.1;
                $this->defense *= 1.7;
                $this->speed *= 1.8;
                break;
            case 'Bankai':
                $this->attack *= 1.7;
                $this->defense *= 1.5;
                $this->speed *= 1.6;
                break;
            case 'Ichigo Arrancar':
                $this->attack *= 1.9;
                $this->defense *= 1.6;
                $this->speed *= 1.8;
                break;
            case 'HEIAN ERA':
                $this->attack *= 1.8;
                $this->defense *= 1.6;
                $this->speed *= 1.7;
                break;
        }
        
        return true;
    }
    
    public function maintainForm() {
        $energyLost = 0;
        
        // Verifica se tem energia suficiente para manter a forma
        if ($this->currentForm !== 'Normal' && $this->currentEnergy <= 5) {
            $this->revertToNormal();
            return ['reverted' => true, 'energyLost' => 0];
        }
        
        // Aplica custo de manutenção (10% do custo de ativação)
        if ($this->currentForm !== 'Normal' && $this->formCost > 0) {
            $maintenanceCost = max(1, floor($this->formCost * 0.1));
            $oldEnergy = $this->currentEnergy;
            $this->currentEnergy = max(0, $this->currentEnergy - $maintenanceCost);
            $energyLost = $oldEnergy - $this->currentEnergy;
            
            // Verifica novamente após o custo
            if ($this->currentEnergy <= 5) {
                $this->revertToNormal();
                return ['reverted' => true, 'energyLost' => $energyLost];
            }
        }
        
        return ['reverted' => false, 'energyLost' => $energyLost];
    }
    
    public function revertToNormal() {
        $this->currentForm = 'Normal';
        $this->formCost = 0;
        // Reset stats para valores base (seria necessário implementar método para resetar stats)
    }
    
    public function setDefending($defending) {
        $this->isDefending = $defending;
    }
    
    public function getCriticalChance() {
        // Define chance de crítico base por personagem
        switch ($this->type) {
            case 'Naruto':
                return 8; // 8% base
            case 'Goku':
                return 10; // 10% base
            case 'Ichigo':
                return 12; // 12% base
            case 'Sukuna':
                return 15; // 15% base
            case 'Subaru':
                return 7; // 7% base
            default:
                return 8; // 8% padrão
        }
    }
    
    public function canRegenerateEnergy() {
        // Goku só regenera se estiver defendendo (não em forma Normal)
        if ($this->type === 'Goku') {
            return $this->isDefending;
        }
        
        // Naruto sempre pode regenerar (por causa da Kurama)
        if ($this->type === 'Naruto') {
            return true;
        }
        
        // Outros só regeneram se estiverem na forma Normal ou defendendo
        return $this->currentForm === 'Normal' || $this->isDefending;
    }
    
    protected function playBurnSound() {
        $audioFile = __DIR__ . '/../Audios/gta-san-andreas-cj-on-fire-sound.mp3';
        
        // Verifica se o arquivo de áudio existe
        if (file_exists($audioFile)) {
            // Toca o áudio por 5 segundos em background, sem bloquear
            $command = "timeout 5s ffplay -nodisp -autoexit '$audioFile' >/dev/null 2>&1 &";
            exec($command);
        }
    }
    
    abstract public function getSpecialMoves();
    abstract public function getForms();
}
?>