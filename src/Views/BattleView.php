<?php
/**
 * BattleView - Visualização da Batalha
 * Contém toda a interface visual da batalha
 */

class BattleView {
    private $gameView;
    private $animationService;
    
    public function __construct() {
        $this->gameView = new GameView();
        $this->animationService = new AnimationService();
    }
    
    /**
     * Mostra tela de início da batalha
     */
    public function showBattleStart($p1Name, $p2Name, $char1Type, $char2Type) {
        echo GameView::MAGENTA . GameView::BOLD . "
╔════════════════════════════════════════════════════════════════╗
║                  INICIANDO BATALHA                             ║
║  {$p1Name} ({$char1Type}) VS {$p2Name} ({$char2Type})           ║
╚════════════════════════════════════════════════════════════════╝
        " . GameView::RESET . "\n\n";
        
        sleep(2);
    }
    
    /**
     * Exibe o status completo da batalha
     */
    public function displayBattle($state) {
        // Não limpa mais a tela
        
        // Cabeçalho do turno
        echo GameView::CYAN . GameView::BOLD . "TURNO " . $state['turn'] . "\n" . GameView::RESET;
        $this->gameView->showSeparator("=", 60);
        
        // Exibe status dos jogadores
        $this->displayPlayerStatus($state['player1'], 1);
        $this->displayPlayerStatus($state['player2'], 2);
        
        // Histórico de eventos
        $this->displayHistory($state['log']);
    }
    
    /**
     * Exibe status de um jogador
     */
    private function displayPlayerStatus($player, $playerNumber) {
        $color = ($playerNumber === 1) ? GameView::GREEN : GameView::BLUE;
        $char = $player['character'];
        
        echo $color . GameView::BOLD . $player['name'] . GameView::RESET . "\n";
        echo "┌─────────────────────────────────────────────────────────────────────┐\n";
        echo "│ " . $char->getType() . " | " . $char->getCurrentForm() . 
             str_repeat(" ", 61 - strlen($char->getType()) - strlen($char->getCurrentForm()) - 3) . "│\n";
        echo "├─────────────────────────────────────────────────────────────────────┤\n";
        
        // HP
        $hpPercent = ($char->getCurrentHp() / $char->getMaxHp()) * 100;
        echo "│ " . GameView::RED . "❤️ " . $this->gameView->drawBar($hpPercent, 30) . " " . 
             sprintf("%4d", $char->getCurrentHp()) . GameView::RESET . " │ ";
        
        // Energia
        $energyPercent = ($char->getCurrentEnergy() / $char->getMaxEnergy()) * 100;
        echo GameView::CYAN . "⚡ " . $this->gameView->drawBar($energyPercent, 30) . " " . 
             sprintf("%4d", $char->getCurrentEnergy()) . GameView::RESET . 
             str_repeat(" ", 9) . "│\n";
        
        echo "└─────────────────────────────────────────────────────────────────────┘\n";
        
        // Efeitos ativos
        $this->displayActiveEffects($char);
        
        echo "\n";
    }
    
    /**
     * Exibe efeitos ativos do personagem
     */
    private function displayActiveEffects($character) {
        $activeEffects = $character->getEffects();
        if (!empty($activeEffects)) {
            echo "┌─────────────────────────────────────────────────────────────────────┐\n";
            echo "│ " . GameView::MAGENTA . "⚡ EFEITOS" . GameView::RESET . str_repeat(" ", 54) . "│\n";
            echo "├─────────────────────────────────────────────────────────────────────┤\n";
            
            $effectLine = "│";
            foreach ($activeEffects as $effect) {
                $icon = "";
                $duration = $effect['duration'] ?? 0;
                switch($effect['type']) {
                    case 'burn': $icon = "🔥"; break;
                    case 'bleed': $icon = "🩸"; break;
                    case 'domain': $icon = "🏯"; break;
                }
                $effectLine .= " $icon($duration)";
            }
            
            echo $effectLine . str_repeat(" ", 67 - strlen($effectLine)) . "│\n";
            echo "└─────────────────────────────────────────────────────────────────────┘\n";
        }
    }
    
    /**
     * Exibe histórico de eventos
     */
    private function displayHistory($log) {
        $this->gameView->showSeparator("═", 69);
        echo GameView::YELLOW . "⚡ HISTÓRICO (últimos 5 eventos)" . GameView::RESET . "\n";
        $this->gameView->showSeparator("─", 69);
        
        $logs = array_slice($log, -5);
        foreach ($logs as $logEntry) {
            echo "│ " . str_pad($logEntry, 65, " ", STR_PAD_RIGHT) . "│\n";
        }
        
        echo "└─────────────────────────────────────────────────────────────────────┘\n\n";
    }
    
    /**
     * Mostra cabeçalho do turno
     */
    public function showTurnHeader($player, $playerName) {
        $color = ($player === 1) ? GameView::GREEN : GameView::BLUE;
        echo "\n" . $color . GameView::BOLD . "TURNO DO " . strtoupper($playerName) . "\n" . GameView::RESET;
    }
    
    /**
     * Mostra resultado de uma ação
     */
    public function showActionResult($result) {
        if (isset($result['message'])) {
            echo "\n" . GameView::YELLOW . $result['message'] . GameView::RESET . "\n";
        }
        
        // Mostra animação ASCII se houver
        if (isset($result['animation'])) {
            $this->animationService->showAttack($result['animation']);
        }
        
        // Pequena pausa
        sleep(1);
    }
    
    /**
     * Mostra tela de batalha cancelada
     */
    public function showBattleCancelled() {
        $this->gameView->clearScreen();
        
        echo GameView::YELLOW . GameView::BOLD . "
╔════════════════════════════════════════════════════════════════╗
║                                                                ║
║                      🚫 BATALHA CANCELADA! 🚫                      ║
║                                                                ║
║  " . str_pad("A batalha foi cancelada pelos jogadores", 47) . "║
║                                                                ║
║  " . str_pad("Voltando ao menu principal...", 47) . "║
║                                                                ║
╚════════════════════════════════════════════════════════════════╝
        " . GameView::RESET . "\n\n";
        
        sleep(2);
    }
    
    /**
     * Mostra tela de fim da batalha
     */
    public function showBattleEnd($winner) {
        $this->gameView->clearScreen();
        
        $winnerColor = ($winner['player'] === 1) ? GameView::GREEN : GameView::BLUE;
        
        echo $winnerColor . GameView::BOLD . "
╔════════════════════════════════════════════════════════════════╗
║                                                                ║
║                      BATALHA FINALIZADA!                       ║
║                                                                ║
║  VENCEDOR: " . str_pad(strtoupper($winner['name']), 47) . "║
║                                                                ║
╚════════════════════════════════════════════════════════════════╝
        " . GameView::RESET . "\n\n";
        
        // Mostra ASCII do personagem vencedor
        $characterASCII = $this->animationService->getCharacterASCII(strtolower($winner['character']));
        if ($characterASCII) {
            echo $winnerColor . GameView::BOLD . "PERSONAGEM VENCEDOR:\n" . GameView::RESET;
            echo $characterASCII . "\n\n";
        }
        
        // Estatísticas finais
        $this->showFinalStats($winner);
        
        // Aguarda entrada para voltar
        $this->gameView->showWaiting();
    }
    
    /**
     * Mostra estatísticas finais
     */
    private function showFinalStats($winner) {
        echo GameView::YELLOW . GameView::BOLD . "ESTADISTICAS FINAIS\n" . GameView::RESET;
        $this->gameView->showSeparator("=", 60);
        
        echo GameView::GREEN . GameView::BOLD . "VENCEDOR: " . GameView::RESET . 
             $winner['name'] . " (" . $winner['character'] . ")\n";
        echo "HP Final: " . $winner['hp'] . "/" . $winner['maxHp'] . "\n";
        echo "Turnos: " . $winner['turns'] . "\n\n";
        
        $this->gameView->showSeparator("=", 60);
    }
}
