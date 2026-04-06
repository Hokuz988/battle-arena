<?php
/**
 * MenuView - Visualização de Menus
 * Contém todos os echos e interfaces de menu
 */

class MenuView {
    private $gameView;
    private $animationService;
    
    public function __construct() {
        $this->gameView = new GameView();
        $this->animationService = new AnimationService();
    }
    
    /**
     * Mostra o menu principal
     */
    public function showMainMenu() {
        $this->gameView->clearScreen();
        $this->gameView->showWelcome();
        
        echo GameView::YELLOW . GameView::BOLD . "\n MENU PRINCIPAL\n" . GameView::RESET;
        $this->gameView->showSeparator("=", 60);
        echo "  [1] BATALHAR\n";
        echo "  [2] VER RANKING\n";
        echo "  [0] SAIR\n\n";
        echo "Opcao: ";
    }
    
    /**
     * Mostra cabeçalho de seleção de jogador
     */
    public function showPlayerSelectionHeader($playerNumber) {
        echo "\n" . ($playerNumber === 1 ? GameView::GREEN : GameView::BLUE) . GameView::BOLD . 
             "JOGADOR $playerNumber ESCOLHA SEU PERSONAGEM\n" . GameView::RESET;
        $this->gameView->showSeparator("-", 60);
    }
    
    /**
     * Mostra lista de personagens disponíveis
     */
    public function showCharacterList($characters) {
        echo "\nEscolha seu personagem:\n";
        $index = 1;
        foreach ($characters as $id => $data) {
            echo "  [" . $index . "] " . $data['name'] . " (" . $data['anime'] . ")\n";
            $index++;
        }
        echo "\nOpcao: ";
    }
    
    /**
     * Mostra ASCII art do personagem selecionado
     */
    public function showCharacterASCII($characterId) {
        $ascii = $this->animationService->getCharacterASCII($characterId);
        if ($ascii) {
            $this->gameView->clearScreen();
            echo "\n\n\n" . $ascii . "\n\n\n";
            $this->gameView->showWaiting();
        }
    }
    
    /**
     * Mostra placeholder do ranking
     */
    public function showRankingPlaceholder() {
        $this->gameView->showHeader("RANKING DE JOGADORES");
        echo "  Ranking ainda nao implementado.\n";
        echo "  Volte apos ganhar batalhas!\n\n";
        $this->gameView->showWaiting();
    }
    
    /**
     * Mostra cabeçalho do menu de ações
     */
    public function showActionMenuHeader($player) {
        echo "\n[1]ATACAR [2]DEFENDER [3]HABILIDADES [4]FORMAS [5]STATUS [9]CANCELAR [0]VOLTAR\n";
        echo "Opcao: ";
    }
    
    /**
     * Mostra menu de habilidades especiais
     */
    public function showSpecialMovesMenu($abilities, $character) {
        echo "\nHABILIDADES ESPECIAIS:\n";
        $idx = 1;
        foreach ($abilities as $key => $ability) {
            $canUse = $character->getCurrentEnergy() >= $ability['cost'] ? "✓" : "✗";
            echo sprintf("[%d]%s %s (%d EN) ", $idx, $canUse, $ability['name'], $ability['cost']);
            echo "- " . $ability['description'] . "\n";
            $idx++;
        }
        echo "[0]VOLTAR\nOpcao: ";
    }
    
    /**
     * Mostra menu de formas
     */
    public function showFormsMenu($forms, $character) {
        echo "\nFORMAS DISPONIVEIS:\n";
        $idx = 1;
        foreach ($forms as $key => $form) {
            $current = ($form['name'] === $character->getCurrentForm()) ? " [ATUAL]" : "";
            $canUse = $character->getCurrentEnergy() >= $form['cost'] ? "✓" : "✗";
            echo "[$idx]$canUse {$form['name']} (Custo: {$form['cost']} EN)$current\n";
            $idx++;
        }
        echo "[0]VOLTAR\nOpcao: ";
    }
    
    /**
     * Mostra status detalhado do personagem
     */
    public function showCharacterStatus($character) {
        echo "\n" . GameView::BOLD . $character->getName() . GameView::RESET . "\n";
        $this->gameView->showSeparator("=", 60);
        echo sprintf("Personagem: %s (%s)\n", $character->getType(), $character->getCurrentForm());
        echo sprintf("HP: %d/%d | Energia: %d/%d\n", 
                    $character->getCurrentHp(), $character->getMaxHp(), 
                    $character->getCurrentEnergy(), $character->getMaxEnergy());
        echo sprintf("ATK: %d | DEF: %d | SPD: %d\n\n", 
                    $character->getAttack(), $character->getDefense(), $character->getSpeed());
        
        // Mostra efeitos ativos
        $effects = $character->getEffects();
        if (!empty($effects)) {
            echo "Efeitos ativos: " . GameView::MAGENTA;
            foreach ($effects as $effect) {
                $icon = "";
                switch($effect['type']) {
                    case 'burn': $icon = "🔥"; break;
                    case 'bleed': $icon = "🩸"; break;
                    case 'domain': $icon = "🏯"; break;
                }
                echo "$icon({$effect['duration']}) ";
            }
            echo GameView::RESET . "\n\n";
        }
    }
    
    /**
     * Mostra ASCII art da habilidade
     */
    public function showAbilityASCII($abilityId) {
        $ascii = $this->animationService->getAbilityASCII($abilityId);
        if ($ascii) {
            echo "\n" . $ascii . "\n";
            sleep(2);
        }
    }
    
    /**
     * Mostra ASCII art da forma
     */
    public function showFormASCII($formId) {
        $ascii = $this->animationService->getFormASCII($formId);
        if ($ascii) {
            echo "\n" . $ascii . "\n";
            sleep(2);
        }
    }
    
    /**
     * Mostra mensagem de erro
     */
    public function showError($message) {
        $this->gameView->showError($message);
    }
    
    /**
     * Mostra mensagem informativa
     */
    public function showInfo($message) {
        $this->gameView->showInfo($message);
    }
}
