<?php
/**
 * MenuController - Controlador de Menus
 * Gerencia toda a interação com menus do jogo
 */

class MenuController {
    private $menuView;
    private $characterModel;
    private $inputService;
    
    public function __construct() {
        $this->menuView = new MenuView();
        $this->characterModel = new CharacterModel();
        $this->inputService = new InputService();
    }
    
    /**
     * Mostra o menu principal e retorna a escolha
     */
    public function showMainMenu() {
        while (true) {
            $this->menuView->showMainMenu();
            $choice = $this->inputService->readChar();
            
            // Validação básica
            if (in_array($choice, ['1', '2', '0'])) {
                return $choice;
            }
            
            $this->menuView->showError("OPCAO INVALIDA! Digite 1, 2 ou 0");
            sleep(1);
        }
    }
    
    /**
     * Mostra o menu de seleção de jogadores
     */
    public function showPlayerSelection() {
        $players = [];
        
        // Obtém lista de personagens do banco de dados
        $availableCharacters = $this->characterModel->getAvailableCharacters();
        
        for ($p = 1; $p <= 2; $p++) {
            $this->menuView->showPlayerSelectionHeader($p);
            
            // Nome do jogador
            $name = $this->inputService->readLine("Digite seu nome (padrao: Player $p): ");
            $players["player{$p}"]['name'] = empty($name) ? "Player $p" : $name;
            
            // Seleção de personagem
            $characterIndex = $this->showCharacterSelection($availableCharacters, $p);
            $selectedCharacter = array_keys($availableCharacters)[$characterIndex - 1];
            
            $players["player{$p}"]['character'] = $selectedCharacter;
            
            // Mostra ASCII do personagem selecionado
            $this->menuView->showCharacterASCII($selectedCharacter);
        }
        
        return $players;
    }
    
    /**
     * Mostra seleção de personagem para um jogador
     */
    private function showCharacterSelection($characters, $playerNumber) {
        $this->menuView->showCharacterList($characters);
        
        while (true) {
            $choice = $this->inputService->readChar();
            
            if (is_numeric($choice) && $choice >= 1 && $choice <= count($characters)) {
                return (int)$choice;
            }
            
            $this->menuView->showError("OPCAO INVALIDA! Escolha entre 1 e " . count($characters));
            sleep(1);
        }
    }
    
    /**
     * Mostra o ranking
     */
    public function showRanking() {
        // TODO: Implementar sistema de ranking
        $this->menuView->showRankingPlaceholder();
    }
    
    /**
     * Mostra menu de ações em batalha
     */
    public function showActionMenu($player, $character) {
        while (true) {
            $this->menuView->showActionMenuHeader($player);
            $choice = $this->inputService->readChar();
            
            switch ($choice) {
                case '1':
                    return 'attack';
                case '2':
                    return 'defend';
                case '3':
                    return $this->showSpecialMoves($character);
                case '4':
                    return $this->showForms($character);
                case '5':
                    $this->menuView->showCharacterStatus($character);
                    break;
                case '9':
                    // Opção de cancelar batalha
                    $this->menuView->showError("CANCELAR BATALHA?");
                    echo "[1] Sim - Cancelar e voltar ao menu\n";
                    echo "[0] Não - Continuar batalha\n";
                    echo "Opcao: ";
                    
                    $confirm = $this->inputService->readChar();
                    if ($confirm === '1') {
                        $this->menuView->showInfo("Batalha cancelada. Voltando ao menu principal...");
                        return 'cancel_battle';
                    }
                    break;
                case '0':
                    $this->menuView->showInfo("Voltando ao menu de ação...");
                    break;
                default:
                    $this->menuView->showError("OPCAO INVALIDA! Digite 1, 2, 3, 4, 5, 9 ou 0");
                    sleep(1);
            }
        }
    }
    
    /**
     * Mostra menu de habilidades especiais
     */
    private function showSpecialMoves($character) {
        $abilities = $this->characterModel->getCharacterAbilities($character->getType());
        
        while (true) {
            $this->menuView->showSpecialMovesMenu($abilities, $character);
            $choice = $this->inputService->readChar();
            
            if ($choice === '0') {
                $this->menuView->showInfo("Voltando ao menu de ação...");
                return null;
            }
            
            if (is_numeric($choice) && $choice >= 1 && $choice <= count($abilities)) {
                $abilityKeys = array_keys($abilities);
                $selectedAbility = $abilityKeys[$choice - 1];
                
                // Verifica se pode usar
                if ($this->characterModel->canUseAbility(
                    $character->getType(), 
                    $selectedAbility, 
                    $character->getCurrentEnergy(),
                    $character->getCurrentHp(),
                    $character->getCurrentForm()
                )) {
                    // Mostra ASCII da habilidade
                    $this->menuView->showAbilityASCII($selectedAbility);
                    return $selectedAbility;
                } else {
                    $this->menuView->showError("ENERGIA INSUFICIENTE ou condicao nao atendida!");
                    sleep(1);
                }
            } else {
                $this->menuView->showError("OPCAO INVALIDA! Digite 1-" . count($abilities) . " ou 0");
                sleep(1);
            }
        }
    }
    
    /**
     * Mostra menu de transformações
     */
    private function showForms($character) {
        $forms = $this->characterModel->getCharacterForms($character->getType());
        
        while (true) {
            $this->menuView->showFormsMenu($forms, $character);
            $choice = $this->inputService->readChar();
            
            if ($choice === '0') {
                $this->menuView->showInfo("Voltando ao menu de ação...");
                return null;
            }
            
            if (is_numeric($choice) && $choice >= 1 && $choice <= count($forms)) {
                $formKeys = array_keys($forms);
                $selectedForm = $formKeys[$choice - 1];
                
                // Verifica energia
                $formCost = $this->characterModel->getFormCost($character->getType(), $selectedForm);
                if ($character->getCurrentEnergy() >= $formCost) {
                    // Mostra ASCII da transformação
                    $this->menuView->showFormASCII($selectedForm);
                    return 'transform_' . $selectedForm;
                } else {
                    $this->menuView->showError("ENERGIA INSUFICIENTE! Você precisa de {$formCost} EN");
                    sleep(1);
                }
            } else {
                $this->menuView->showError("OPCAO INVALIDA! Digite 1-" . count($forms) . " ou 0");
                sleep(1);
            }
        }
    }
}
