<?php
/**
 * BattleController - Controlador da Batalha
 * Gerencia toda a lógica e fluxo da batalha
 */

class BattleController {
    private $battleModel;
    private $battleView;
    private $menuController;
    private $animationService;
    
    public function __construct() {
        $this->battleModel = new BattleModel();
        $this->battleView = new BattleView();
        $this->menuController = new MenuController();
        $this->animationService = new AnimationService();
    }
    
    /**
     * Inicia uma nova batalha
     */
    public function startBattle($p1Name, $p2Name, $char1Type, $char2Type) {
        try {
            // Inicializa a batalha
            $this->battleModel->initialize($p1Name, $p2Name, $char1Type, $char2Type);
            
            // Limpa tela e mostra início
            system('clear');
            $this->battleView->showBattleStart($p1Name, $p2Name, $char1Type, $char2Type);
            sleep(2);
            
            // Executa o loop da batalha
            $this->runBattleLoop();
            
            // Mostra o resultado
            $this->battleView->showBattleEnd($this->battleModel->getWinner());
            return 'completed';
            
        } catch (Exception $e) {
            throw new Exception("Erro na batalha: " . $e->getMessage());
        }
    }
    
    /**
     * Loop principal da batalha
     */
    private function runBattleLoop() {
        while (!$this->battleModel->isGameOver()) {
            // Exibe status da batalha
            $this->battleView->displayBattle($this->battleModel->getState());
            
            // Aplica efeitos contínuos
            $this->battleModel->applyOngoingEffects();
            
            // Verifica se alguém morreu após efeitos
            if ($this->battleModel->isGameOver()) {
                break;
            }
            
            // Turno do Player 1
            $result1 = $this->executePlayerTurn(1);
            
            // Verificar se batalha foi cancelada
            if ($result1 === 'cancelled') {
                return 'cancelled';
            }
            
            // Verifica vitória após turno 1
            if ($this->battleModel->isGameOver()) {
                break;
            }
            
            // Pequena pausa entre turnos
            usleep(500000);
            
            // Turno do Player 2
            $result2 = $this->executePlayerTurn(2);
            
            // Verificar se batalha foi cancelada
            if ($result2 === 'cancelled') {
                return 'cancelled';
            }
            
            // Incrementa turno
            $this->battleModel->nextTurn();
        }
        
        // Fim da batalha
        $this->battleView->showBattleEnd($this->battleModel->getWinner());
        
        return 'completed';
    }
    
    /**
     * Executa o turno de um jogador
     */
    private function executePlayerTurn($player) {
        $character = $this->battleModel->getPlayerCharacter($player);
        
        // Mostra cabeçalho do turno
        $this->battleView->showTurnHeader($player, $character->getName());
        
        // Loop para permitir retry quando usuário cancela ação
        while (true) {
            // Obtém ação do menu
            $action = $this->menuController->showActionMenu($player, $character);
            
            if ($action !== null) {
                // Verificar se é cancelamento de batalha
                if ($action === 'cancel_battle') {
                    $this->battleView->showBattleCancelled();
                    return 'cancelled';
                }
                
                // Executa ação
                $result = $this->battleModel->executeAction($player, $action);
                
                // Se for ação completa, retorna resultado
                if ($result) {
                    // Adiciona informações de ataque para animação
                    if (!isset($result['attacker'])) {
                        $result['attacker'] = $this->battleModel->getPlayerCharacter($player);
                        $result['defender'] = $this->battleModel->getPlayerCharacter($player === 1 ? 2 : 1);
                        $result['attackType'] = $this->getAttackTypeFromAction($action);
                    }
                    
                    $this->battleView->showActionResult($result);
                    
                    // Mostra animação se houver
                    if (isset($result['animation'])) {
                        $this->showActionAnimation($result['animation']);
                    }
                }
                
                // Separador visual
                echo "\n" . str_repeat("-", 60) . "\n";
                break; // Sai do loop, ação executada com sucesso
                
            } else {
                // Se action for null (usuário voltou do menu), mostra mensagem e continua o loop
                echo "\n" . GameView::CYAN . GameView::BOLD . "ℹ️  Ação cancelada. Escolha outra opção." . GameView::RESET . "\n";
                sleep(1);
                // Continua o loop, mostra o menu novamente
                continue;
            }
        }
    }
    
    /**
     * Mostra animação baseada na ação
     */
    private function showActionAnimation($animationType) {
        switch ($animationType) {
            case 'attack':
                $this->animationService->showAttackWithGif('normal');
                break;
            case 'special':
                $this->animationService->showAttackWithGif('special');
                break;
            case 'ultimate':
                $this->animationService->showAttackWithGif('ultimate');
                break;
            case 'blackflash':
                $this->animationService->showBlackFlash();
                break;
            case 'dodge':
                $this->animationService->showDodge();
                break;
            case 'heal':
                $this->animationService->showHealEffect(100);
                break;
        }
        
        usleep(300000);
    }
    
    /**
     * Obtém tipo de ataque a partir da ação
     */
    private function getAttackTypeFromAction($action) {
        if (strpos($action, 'transform_') === 0) {
            return 'transform';
        }
        
        // Mapear ações para tipos de ataque
        $attackMap = [
            'attack' => 'normal',
            'defend' => 'defend',
            'special' => 'special',
            'ultimate' => 'ultimate'
        ];
        
        // Verificar se é uma habilidade especial
        if (is_string($action) && !isset($attackMap[$action])) {
            return 'special'; // Habilidades especiais
        }
        
        return $attackMap[$action] ?? 'normal';
    }
}
