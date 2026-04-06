<?php
/**
 * BattleModel - Modelo de Dados da Batalha
 * Gerencia o estado e lógica da batalha
 */

class BattleModel {
    private $battleManager;
    private $characterModel;
    private $turn = 1;
    
    public function __construct() {
        $this->characterModel = new CharacterModel();
    }
    
    /**
     * Inicializa a batalha
     */
    public function initialize($p1Name, $p2Name, $char1Type, $char2Type) {
        try {
            // Criar personagens usando o banco de dados
            $char1 = $this->characterModel->createCharacter($char1Type, $p1Name . "'s " . ucfirst($char1Type));
            $char2 = $this->characterModel->createCharacter($char2Type, $p2Name . "'s " . ucfirst($char2Type));
            
            // Inicializar BattleManager existente
            $this->battleManager = new BattleManager($p1Name, $p2Name, $char1Type, $char2Type);
            
        } catch (Exception $e) {
            throw new Exception("Erro ao inicializar batalha: " . $e->getMessage());
        }
    }
    
    /**
     * Obtém o estado completo da batalha
     */
    public function getState() {
        if (!$this->battleManager) {
            throw new Exception("Batalha não inicializada");
        }
        
        $state = $this->battleManager->getState();
        $state['turn'] = $this->turn;
        return $state;
    }
    
    /**
     * Obtém personagem do jogador
     */
    public function getPlayerCharacter($player) {
        $state = $this->getState();
        return ($player === 1) ? $state['player1']['character'] : $state['player2']['character'];
    }
    
    /**
     * Obtém dados do jogador
     */
    public function getPlayerData($player) {
        $state = $this->getState();
        return ($player === 1) ? $state['player1'] : $state['player2'];
    }
    
    /**
     * Executa uma ação
     */
    public function executeAction($player, $action) {
        if (!$this->battleManager) {
            throw new Exception("Batalha não inicializada");
        }
        
        try {
            // Processa ação especial de transformação
            if (strpos($action, 'transform_') === 0) {
                $formId = substr($action, 10); // Remove 'transform_'
                return $this->handleTransformation($player, $formId);
            }
            
            // Executa ação normal
            $result = $this->battleManager->setAction($player, $action);
            
            // Se for ação completa, retorna resultado
            if (isset($result['actions'])) {
                return $this->processActionResult($result, $player);
            }
            
            return ['status' => 'waiting'];
            
        } catch (Exception $e) {
            throw new Exception("Erro ao executar ação: " . $e->getMessage());
        }
    }
    
    /**
     * Processa resultado da ação
     */
    private function processActionResult($result, $player) {
        $actions = $result['actions'] ?? [];
        $playerAction = null;
        
        // Encontra a ação do jogador específico
        foreach ($actions as $action) {
            if (isset($action['player']) && $action['player'] == $player) {
                $playerAction = $action;
                break;
            }
        }
        
        if ($playerAction) {
            return [
                'message' => $playerAction['message'] ?? '',
                'damage' => $playerAction['damage'] ?? 0,
                'healing' => $playerAction['healing'] ?? 0,
                'energyCost' => $this->getActionEnergyCost($player, $playerAction),
                'animation' => $playerAction['animation'] ?? 'attack',
                'critical' => isset($playerAction['critical']) && $playerAction['critical']
            ];
        }
        
        return null;
    }
    
    /**
     * Obtém custo de energia de uma ação
     */
    private function getActionEnergyCost($player, $action) {
        $character = $this->getPlayerCharacter($player);
        
        // Se for transformação, obtém custo da forma
        if (isset($action['character']) && method_exists($character, 'getCurrentForm')) {
            $characterType = $character->getType();
            $currentForm = $character->getCurrentForm();
            
            return $this->characterModel->getFormCost($characterType, $currentForm);
        }
        
        // Para habilidades especiais
        if (isset($action['message'])) {
            // Tenta extrair da mensagem ou usar lógica específica
            return 0; // Por enquanto, retorna 0
        }
        
        return 0;
    }
    
    /**
     * Handle especial para transformações
     */
    private function handleTransformation($player, $formId) {
        $character = $this->getPlayerCharacter($player);
        $characterType = strtolower($character->getType());
        
        // Verifica se pode transformar
        $formCost = $this->characterModel->getFormCost($characterType, $formId);
        if ($character->getCurrentEnergy() < $formCost) {
            return [
                'message' => "⚡ ENERGIA INSUFICIENTE! Você precisa de {$formCost} EN",
                'damage' => 0
            ];
        }
        
        // Aplica transformação
        if (method_exists($character, 'applyForm')) {
            $formName = $this->getFormNameById($characterType, $formId);
            
            if ($character->applyForm($formName)) {
                return [
                    'message' => "{$character->getName()} se transformou para {$formName}!",
                    'damage' => 0,
                    'energyCost' => $formCost,
                    'animation' => 'transform'
                ];
            }
        }
        
        return [
            'message' => "Falha ao transformar!",
            'damage' => 0
        ];
    }
    
    /**
     * Obtém nome da forma pelo ID
     */
    private function getFormNameById($characterType, $formId) {
        $forms = $this->characterModel->getCharacterForms($characterType);
        
        foreach ($forms as $id => $form) {
            if ($id === $formId) {
                return $form['name'];
            }
        }
        
        return 'Normal';
    }
    
    /**
     * Aplica efeitos contínuos
     */
    public function applyOngoingEffects() {
        if (!$this->battleManager) {
            return;
        }
        
        try {
            $state = $this->getState();
            
            // Aplica efeitos para ambos os jogadores
            for ($p = 1; $p <= 2; $p++) {
                $char = $this->getPlayerCharacter($p);
                
                // Regeneração de energia
                if (method_exists($char, 'regenerateEnergy')) {
                    $oldEnergy = $char->getCurrentEnergy();
                    $char->regenerateEnergy();
                    $newEnergy = $char->getCurrentEnergy();
                    
                    if ($newEnergy > $oldEnergy) {
                        $playerData = $this->getPlayerData($p);
                        $state['log'][] = GameView::CYAN . $playerData['name'] . 
                                         ": Regenerou " . ($newEnergy - $oldEnergy) . " de energia" . GameView::RESET;
                    }
                }
                
                // Aplica efeitos de status
                if (method_exists($char, 'applyEffects')) {
                    $messages = $char->applyEffects();
                    
                    foreach ($messages as $message) {
                        $playerData = $this->getPlayerData($p);
                        $state['log'][] = GameView::RED . $playerData['name'] . ": " . $message . GameView::RESET;
                    }
                }
            }
            
        } catch (Exception $e) {
            // Erros em efeitos não devem parar a batalha
            error_log("Erro em efeitos contínuos: " . $e->getMessage());
        }
    }
    
    /**
     * Verifica se a batalha acabou
     */
    public function isGameOver() {
        if (!$this->battleManager) {
            return false;
        }
        
        $state = $this->getState();
        return $state['gameOver'];
    }
    
    /**
     * Obtém informações do vencedor
     */
    public function getWinner() {
        if (!$this->battleManager || !$this->isGameOver()) {
            return null;
        }
        
        $winner = $this->battleManager->getWinner();
        $state = $this->getState();
        
        return [
            'player' => ($winner['name'] === $state['player1']['name']) ? 1 : 2,
            'name' => $winner['name'],
            'character' => $winner['character'],
            'hp' => $winner['hp'],
            'maxHp' => $this->getPlayerCharacter(($winner['name'] === $state['player1']['name']) ? 1 : 2)->getMaxHp(),
            'turns' => $this->turn
        ];
    }
    
    /**
     * Avança para o próximo turno
     */
    public function nextTurn() {
        $this->turn++;
    }
    
    /**
     * Reinicia a batalha
     */
    public function reset() {
        $this->battleManager = null;
        $this->turn = 1;
    }
}
