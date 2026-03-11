<?php
// managers/BattleManager.php
require_once __DIR__ . '/../classes/Sukuna.php';
require_once __DIR__ . '/../classes/Goku.php';
require_once __DIR__ . '/../classes/Naruto.php';
require_once __DIR__ . '/../classes/Ichigo.php';

class BattleManager {
    private $player1;
    private $player2;
    private $char1;
    private $char2;
    private $turn = 1;
    private $log = [];
    private $actions = [];
    private $ready = [false, false];
    
    public function __construct($p1Name, $p2Name, $char1Type, $char2Type) {
        $this->player1 = $p1Name;
        $this->player2 = $p2Name;
        $this->char1 = $this->createCharacter($char1Type, $p1Name . "'s " . ucfirst($char1Type));
        $this->char2 = $this->createCharacter($char2Type, $p2Name . "'s " . ucfirst($char2Type));
        $this->log[] = "⚔️ Batalha iniciada: {$p1Name} vs {$p2Name}";
    }
    
    private function createCharacter($type, $name) {
        $type = strtolower($type);
        switch($type) {
            case 'sukuna': return new Sukuna($name);
            case 'goku': return new Goku($name);
            case 'naruto': return new Naruto($name);
            case 'ichigo': return new Ichigo($name);
            default: throw new Exception("Personagem inválido: $type");
        }
    }
    
    public function setAction($player, $action) {
        $this->actions[$player] = $action;
        $this->ready[$player-1] = true;
        
        if ($this->ready[0] && $this->ready[1]) {
            return $this->processTurn();
        }
        
        return ['status' => 'waiting', 'player' => $player];
    }
    
    private function processTurn() {
        $results = [];
        
        // Verifica se algum jogador está usando Mugetsu - tem prioridade máxima
        $mugetsuUser = null;
        if (isset($this->actions[1]) && $this->actions[1] === 'mugetsu' && 
            method_exists($this->char1, 'canUseMugetsu') && $this->char1->canUseMugetsu()) {
            $mugetsuUser = 1;
        } elseif (isset($this->actions[2]) && $this->actions[2] === 'mugetsu' && 
                  method_exists($this->char2, 'canUseMugetsu') && $this->char2->canUseMugetsu()) {
            $mugetsuUser = 2;
        }
        
        // Determina ordem: Mugetsu primeiro, depois pela speed
        if ($mugetsuUser !== null) {
            $order = [$mugetsuUser, ($mugetsuUser === 1) ? 2 : 1];
        } else {
            $order = ($this->char1->getSpeed() >= $this->char2->getSpeed()) ? [1, 2] : [2, 1];
        }
        
        foreach ($order as $player) {
            $char = $player == 1 ? $this->char1 : $this->char2;
            $target = $player == 1 ? $this->char2 : $this->char1;
            $action = $this->actions[$player];
            
            $result = null;
            
            if ($action === 'attack') {
                $result = $char->attack($target);
            } elseif ($action === 'defend') {
                $result = $char->defend();
            } else {
                if (method_exists($char, $action)) {
                    $result = $char->$action($target);
                }
            }
            
            if ($result) {
                $result['player'] = $player;
                $result['character'] = $char->getType();
                $results[] = $result;
                
                // Mensagem combinada mais organizada
                $message = $result['message'];
                
                // Adiciona informação de custo de forma se houver
                $formResult = $char->maintainForm();
                if ($formResult['energyLost'] > 0) {
                    $message .= " | Forma gastou " . $formResult['energyLost'] . " de energia";
                }
                if ($formResult['reverted']) {
                    $message .= " | Perdeu a transformação!";
                }
                
                $this->log[] = $message;
            }
            
            if ($this->char1->getCurrentHp() <= 0 || $this->char2->getCurrentHp() <= 0) {
                break;
            }
        }
        
        // Fim do turno - reseta estado de defesa e regenera energia
        $this->char1->setDefending(false);
        $this->char2->setDefending(false);
        $this->char1->regenerateEnergy();
        $this->char2->regenerateEnergy();
        $this->char1->removeDefenseBoost();
        $this->char2->removeDefenseBoost();
        
        $this->ready = [false, false];
        $this->turn++;
        
        return [
            'actions' => $results,
            'gameOver' => ($this->char1->getCurrentHp() <= 0 || $this->char2->getCurrentHp() <= 0),
            'winner' => $this->char1->getCurrentHp() > 0 ? 1 : 2
        ];
    }
    
    public function getState() {
        return [
            'turn' => $this->turn,
            'player1' => [
                'name' => $this->player1,
                'character' => $this->char1
            ],
            'player2' => [
                'name' => $this->player2,
                'character' => $this->char2
            ],
            'log' => $this->log,
            'gameOver' => ($this->char1->getCurrentHp() <= 0 || $this->char2->getCurrentHp() <= 0)
        ];
    }
    
    public function getWinner() {
        if ($this->char1->getCurrentHp() > 0) {
            return [
                'name' => $this->player1,
                'character' => $this->char1->getType(),
                'hp' => $this->char1->getCurrentHp()
            ];
        } else {
            return [
                'name' => $this->player2,
                'character' => $this->char2->getType(),
                'hp' => $this->char2->getCurrentHp()
            ];
        }
    }
    
    public function getStats() {
        return [
            'player1' => [
                'name' => $this->player1,
                'char' => $this->char1->getType(),
                'stats' => method_exists($this->char1, 'getStats') ? $this->char1->getStats() : []
            ],
            'player2' => [
                'name' => $this->player2,
                'char' => $this->char2->getType(),
                'stats' => method_exists($this->char2, 'getStats') ? $this->char2->getStats() : []
            ]
        ];
    }
    
    public function getActions() {
        return $this->actions;
    }
    
    public function getReady() {
        return $this->ready;
    }
    
    public function restoreFromState($state) {
        $this->player1 = $state->player1_name;
        $this->player2 = $state->player2_name;
        $this->turn = $state->turn;
        $this->log = isset($state->log) ? (is_array($state->log) ? $state->log : json_decode($state->log, true)) : [];
        $this->actions = isset($state->actions) ? $state->actions : [];
        $this->ready = isset($state->ready) ? $state->ready : [false, false];
        
        // Recria os personagens
        $this->char1 = $this->createCharacter($state->character1_type, $state->character1_name);
        $this->char2 = $this->createCharacter($state->character2_type, $state->character2_name);
        
        // Restaura os atributos
        $this->char1->setCurrentHp($state->char1_hp);
        $this->char1->setCurrentEnergy($state->char1_energy);
        $this->char2->setCurrentHp($state->char2_hp);
        $this->char2->setCurrentEnergy($state->char2_energy);
    }
}
