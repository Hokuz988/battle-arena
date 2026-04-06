<?php
/**
 * GameController - Controlador Principal do Jogo
 * Orquestra todos os outros controladores
 */

class GameController {
    private $gameView;
    private $menuController;
    private $battleController;
    private $inputService;
    
    public function __construct() {
        $this->gameView = new GameView();
        $this->menuController = new MenuController();
        $this->battleController = new BattleController();
        $this->inputService = new InputService();
    }
    
    /**
     * Inicia o jogo
     */
    public function start() {
        $this->gameView->showWelcome();
        $this->runMainMenu();
    }
    
    /**
     * Executa o menu principal
     */
    private function runMainMenu() {
        while (true) {
            $this->gameView->showWelcome();
            $this->gameView->showMainMenu();
            
            $choice = $this->inputService->readChar();
            
            switch ($choice) {
                case '1':
                    $this->startNewBattle();
                    break;
                case '2':
                    $this->menuController->showRanking();
                    break;
                case '0':
                    $this->gameView->showGoodbye();
                    exit(0); // Sai do PHP e do container Docker
                default:
                    $this->gameView->showError("OPCAO INVALIDA! Digite 1, 2 ou 0");
                    sleep(1);
            }
        }
    }
    
    /**
     * Inicia uma nova batalha
     */
    private function startNewBattle() {
        try {
            // Seleção de jogadores
            $players = $this->menuController->showPlayerSelection();
            
            // Inicia batalha
            $result = $this->battleController->startBattle(
                $players['player1']['name'],
                $players['player2']['name'],
                $players['player1']['character'],
                $players['player2']['character']
            );
            
            // Verificar se batalha foi cancelada
            if ($result === 'cancelled') {
                $this->gameView->showInfo("Batalha cancelada. Voltando ao menu principal...");
                return;
            }
            
        } catch (Exception $e) {
            $this->gameView->showError("Erro ao iniciar batalha: " . $e->getMessage());
        }
    }
}
