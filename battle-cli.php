#!/usr/bin/env php
<?php
/**
 * BATTLE ARENA - TERMINAL GAME v3.0
 * Um jogo de batalha táticas em tempo real no terminal
 * Com ASCII arts integradas, efeitos especiais, formas como buffs, e menu reformulado
 */

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/interfaces/ActionInterface.php';
require_once __DIR__ . '/classes/Character.php';
require_once __DIR__ . '/classes/ASCIIArts.php';
require_once __DIR__ . '/classes/Sukuna.php';
require_once __DIR__ . '/classes/Goku.php';
require_once __DIR__ . '/classes/Naruto.php';
require_once __DIR__ . '/classes/Ichigo.php';
require_once __DIR__ . '/classes/Subaru.php';
require_once __DIR__ . '/managers/BattleManager.php';

const RESET = "\033[0m";
const BOLD = "\033[1m";
const RED = "\033[91m";
const GREEN = "\033[92m";
const YELLOW = "\033[93m";
const BLUE = "\033[94m";
const MAGENTA = "\033[95m";
const CYAN = "\033[96m";
const WHITE = "\033[97m";
const BG_BLACK = "\033[40m";
const BG_RED = "\033[41m";
const BG_GREEN = "\033[42m";
const BG_BLUE = "\033[44m";

class TerminalBattle {
    private $battleManager;
    private $turn = 1;
    private $playerNames = ['Player 1', 'Player 2'];
    private $selectedCharacters = [];
    private $playerForms = ['Normal', 'Normal'];
    private $effects = [];
    private $returnByDeathUsage = [0, 0];
    
    public function __construct() {
        $this->clearScreen();
        $this->showWelcome();
        $this->showMainMenu();
    }
    
    private function clearScreen() {
        system('clear');
    }
    
    private function showWelcome() {
        echo CYAN . BOLD . "
╔════════════════════════════════════════════════════════════════╗
║                   BATTLE ARENA TERMINAL v3.0                   ║
║              Anime Fighters - Strategy Battle Game              ║
║                    Com Efeitos Especiais!                       ║
║  Escolha seus personagens e lutte em epicas batalhas!          ║
╚════════════════════════════════════════════════════════════════╝
        " . RESET . "\n";
    }
    
    // Função para ler input sem precisar de Enter (para opções)
    private function readChar() {
        // Tenta usar comando do sistema para ler um caractere sem Enter
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $input = shell_exec('powershell -Command "$Host.UI.RawUI.ReadKey(\'NoEcho,IncludeKeyDown\')"');
            if ($input) {
                $char = trim(explode(':', $input)[1] ?? '');
                return $char;
            }
        } else {
            // Linux/Mac
            $stty_mode = shell_exec('stty -g');
            shell_exec('stty -icanon -echo');
            $input = fread(STDIN, 1);
            shell_exec('stty ' . $stty_mode);
            
            if ($input !== false && $input !== '') {
                return $input;
            }
        }
        
        // Fallback para readline ou fgets
        if (function_exists('readline')) {
            $input = readline();
            return trim($input);
        }
        
        return trim(fgets(STDIN));
    }
    
    // Função para ler texto completo (para nomes)
    private function readLine($prompt = "") {
        echo $prompt;
        if (function_exists('readline')) {
            return trim(readline());
        }
        return trim(fgets(STDIN));
    }
    
    private function showMainMenu() {
        while (true) {
            $this->clearScreen();
            $this->showWelcome();
            
            echo YELLOW . BOLD . "\n MENU PRINCIPAL\n" . RESET;
            echo str_repeat("=", 60) . "\n\n";
            echo "  [1] BATALHAR\n";
            echo "  [2] VER RANKING\n";
            echo "  [0] SAIR\n\n";
            echo "Opcao: ";
            
            $choice = $this->readChar();
            
            switch ($choice) {
                case '1':
                    $this->selectPlayers();
                    $this->startBattle();
                    break;
                case '2':
                    $this->showRanking();
                    break;
                case '0':
                    $this->clearScreen();
                    echo GREEN . BOLD . "Obrigado por jogar Battle Arena!\n" . RESET;
                    exit(0);
                default:
                    echo RED . BOLD . "❌ OPCAO INVALIDA! Digite 1, 2 ou 0\n" . RESET;
                    sleep(1);
            }
        }
    }
    
    private function showRanking() {
        $this->clearScreen();
        echo YELLOW . BOLD . "RANKING DE JOGADORES\n" . RESET;
        echo str_repeat("=", 60) . "\n\n";
        echo "  Ranking ainda nao implementado.\n";
        echo "  Volte apos ganhar batalhas!\n\n";
        echo "Pressione ENTER para voltar...";
        fgets(STDIN);
    }
    
    private function selectPlayers() {
        $this->clearScreen();
        echo YELLOW . BOLD . "SELECAO DE PERSONAGENS\n" . RESET;
        echo str_repeat("=", 60) . "\n\n";
        
        $characters = [
            'Sukuna', 'Goku', 'Naruto', 'Ichigo', 'Subaru'
        ];
        
        for ($p = 1; $p <= 2; $p++) {
            echo "\n" . ($p === 1 ? GREEN : BLUE) . BOLD . "JOGADOR $p ESCOLHA SEU PERSONAGEM\n" . RESET;
            echo str_repeat("-", 60) . "\n\n";
            
            $name = $this->readLine("Digite seu nome (padrao: Player $p): ");
            $this->playerNames[$p - 1] = empty($name) ? "Player $p" : $name;
            
            echo "\nEscolha seu personagem:\n";
            for ($i = 0; $i < count($characters); $i++) {
                echo "  [" . ($i + 1) . "] " . $characters[$i] . "\n";
            }
            
            while (true) {
                echo "\nOpcao [1-" . count($characters) . "]: ";
                $choice = $this->readChar();
                if (is_numeric($choice) && $choice >= 1 && $choice <= count($characters)) {
                    $this->selectedCharacters[$p - 1] = $characters[$choice - 1];
                    // Mostra ASCII do personagem
                    $characterKey = strtolower($characters[$choice - 1]) . '_character';
                    $ascii = ASCIIArts::getASCII($characterKey);
                    if ($ascii) {
                        system('clear');
                        echo "\n\n\n" . $ascii . "\n\n\n";
                        echo "Pressione ENTER para continuar...";
                        fgets(STDIN);
                    }
                    break;
                }
                echo RED . BOLD . "❌ OPCAO INVALIDA! Escolha entre 1 e " . count($characters) . "\n" . RESET;
            }
        }
        
        $this->clearScreen();
    }
    
    private function startBattle() {
        echo MAGENTA . BOLD . "
╔════════════════════════════════════════════════════════════════╗
║                  INICIANDO BATALHA                             ║
╚════════════════════════════════════════════════════════════════╝
        " . RESET . "\n";
        sleep(1);
        
        $this->battleManager = new BattleManager(
            $this->playerNames[0],
            $this->playerNames[1],
            $this->selectedCharacters[0],
            $this->selectedCharacters[1]
        );
        
        $this->runBattleLoop();
    }
    
    private function runBattleLoop() {
        while (true) {
            // Limpa tela apenas no início de cada turno completo
            $this->clearScreen();
            $this->displayBattle();
            
            $state = $this->battleManager->getState();
            if ($state['player1']['character']->getCurrentHp() <= 0 || 
                $state['player2']['character']->getCurrentHp() <= 0) {
                $this->showBattleEnd();
                break;
            }
            
            $this->applyOngoingEffects();
            
            // Executa turno do jogador 1 sem limpar tela
            $this->executeTurn(1);
            usleep(500000);
            
            // Verifica se o jogo acabou após o turno do jogador 1
            $state = $this->battleManager->getState();
            if ($state['player1']['character']->getCurrentHp() <= 0 || 
                $state['player2']['character']->getCurrentHp() <= 0) {
                $this->showBattleEnd();
                break;
            }
            
            // Executa turno do jogador 2 sem limpar tela
            $this->executeTurn(2);
            usleep(500000);
            
            $this->turn++;
        }
    }
    
    private function displayBattle() {
        $state = $this->battleManager->getState();
        
        echo CYAN . BOLD . "TURNO " . $this->turn . "\n" . RESET;
        echo str_repeat("=", 60) . "\n\n";
        
        for ($p = 1; $p <= 2; $p++) {
            $pData = ($p === 1) ? $state['player1'] : $state['player2'];
            $char = $pData['character'];
            $color = ($p === 1) ? GREEN : BLUE;
            
            $hpPercent = ($char->getCurrentHp() / $char->getMaxHp()) * 100;
            $energyPercent = ($char->getCurrentEnergy() / $char->getMaxEnergy()) * 100;
            
            echo $color . BOLD . $this->playerNames[$p - 1] . RESET . "\n";
            echo "Personagem: " . $char->getType() . " (" . $this->playerForms[$p - 1] . ")\n\n";
            
            echo "❤️  HP:     " . $this->drawBar($hpPercent, 35);
            echo sprintf(" %d/%d\n", $char->getCurrentHp(), $char->getMaxHp());
            
            echo "⚡ Energia: " . $this->drawBar($energyPercent, 35, CYAN);
            echo sprintf(" %d/%d\n", $char->getCurrentEnergy(), $char->getMaxEnergy());
            
            if (!empty($this->effects[$p])) {
                echo "Efeitos: " . MAGENTA . implode(", ", $this->effects[$p]) . RESET . "\n";
            }
            
            echo "\n";
        }
        
        echo str_repeat("=", 60) . "\n";
        echo YELLOW . "HISTORICO (últimos 5 eventos)\n" . RESET;
        
        $logs = array_slice($state['log'], -5);
        foreach ($logs as $log) {
            echo "  $log\n";
        }
        
        echo str_repeat("=", 60) . "\n\n";
    }
    
    private function drawBar($percent, $width, $color = GREEN) {
        $filled = intval(($percent / 100) * $width);
        $empty = $width - $filled;
        
        // Muda cor baseada no percentual
        if ($percent > 50) {
            $barColor = GREEN;
        } elseif ($percent > 20) {
            $barColor = YELLOW;
        } else {
            $barColor = RED;
        }
        
        // Usa caracteres Unicode mais elegantes
        $filledChar = "█";
        $emptyChar = "░";
        
        // Adiciona bordas arredondadas na barra
        $bar = $barColor . "╟" . str_repeat($filledChar, $filled) . str_repeat($emptyChar, $empty) . "╢" . RESET;
        
        // Adiciona percentual formatado
        $percentText = sprintf("%3.0f%%", $percent);
        $percentColor = ($percent > 50) ? GREEN : (($percent > 20) ? YELLOW : RED);
        
        return $bar . " " . $percentColor . $percentText . RESET;
    }
    
    private function executeTurn($player) {
        echo "\n" . ($player === 1 ? GREEN : BLUE) . BOLD . 
             "TURNO DO " . $this->playerNames[$player - 1] . "\n" . RESET;
        
        $state = $this->battleManager->getState();
        $char = ($player === 1) ? $state['player1']['character'] : $state['player2']['character'];
        
        // Mostra menu de ação sem limpar a tela
        $this->showActionMenu($player, $char);
        
        // Adiciona uma separação visual após a ação, mas sem limpar tela
        echo "\n" . str_repeat("-", 60) . "\n";
    }
    
    private function showActionMenu($player, $character) {
        while (true) {
            echo "\n[1]ATACAR [2]DEFENDER [3]HABILIDADES [4]FORMAS [5]STATUS [0]VOLTAR\n";
            echo "Opcao: ";
            
            $choice = $this->readChar();
            
            switch ($choice) {
                case '1':
                    $this->executeAction($player, 'attack');
                    return;
                case '2':
                    $this->executeAction($player, 'defend');
                    return;
                case '3':
                    $this->showSpecialMoves($player, $character);
                    return;
                case '4':
                    $this->showForms($player, $character);
                    return;
                case '5':
                    $this->showDetailedStatus($player, $character);
                    break;
                case '0':
                    echo YELLOW . "Voltando ao menu de ação...\n" . RESET;
                    break;
                default:
                    echo RED . BOLD . "❌ OPCAO INVALIDA! Digite 1, 2, 3, 4, 5 ou 0\n" . RESET;
                    sleep(1);
            }
        }
    }
    
    private function executeAction($player, $action) {
        $state = $this->battleManager->getState();
        $char = ($player === 1) ? $state['player1']['character'] : $state['player2']['character'];
        
        if ($action === 'returnbydeath' && $char->getType() === 'Subaru') {
            if ($this->returnByDeathUsage[$player - 1] < 2) {
                $this->returnByDeathUsage[$player - 1]++;
                $char->setCurrentHp($char->getMaxHp());
                $char->setCurrentEnergy($char->getMaxEnergy());
                unset($this->effects[$player]);
                echo MAGENTA . BOLD . "⏰ RETURN BY DEATH ATIVADO! (uso " . $this->returnByDeathUsage[$player - 1] . "/2)\n" . RESET;
                sleep(1);
                $this->turn = 1;
                $this->effects = [];
                return;
            }
        }
        
        $this->battleManager->setAction($player, $action);
    }
    
    private function applyOngoingEffects() {
        $state = $this->battleManager->getState();
        
        for ($p = 1; $p <= 2; $p++) {
            $char = ($p === 1) ? $state['player1']['character'] : $state['player2']['character'];
            
            // Regeneração de energia específica do personagem
            if (method_exists($char, 'regenerateEnergy')) {
                $oldEnergy = $char->getCurrentEnergy();
                $char->regenerateEnergy();
                $newEnergy = $char->getCurrentEnergy();
                if ($newEnergy > $oldEnergy) {
                    $state['log'][] = CYAN . $this->playerNames[$p - 1] . ": Regenerou " . ($newEnergy - $oldEnergy) . " de energia" . RESET;
                }
            }
            
            // Aplica efeitos contínuos
            $messages = $char->applyEffects();
            
            foreach ($messages as $message) {
                $state['log'][] = RED . $this->playerNames[$p - 1] . ": " . $message . RESET;
            }
        }
    }
    
    private function showForms($player, $character) {
        $forms = $character->getForms();
        $costs = [
            'Sukuna' => ['Normal' => 0, 'HEIAN ERA' => 40],
            'Goku' => ['Normal' => 0, 'Super Saiyajin' => 35, 'Ultra Instinto' => 60],
            'Naruto' => ['Normal' => 0, 'Modo Kurama' => 45, 'Sabio dos Seis Caminhos' => 70],
            'Ichigo' => ['Normal' => 0, 'Bankai' => 50, 'Ichigo Arrancar' => 45],
            'Subaru' => ['Normal' => 0, 'Resolvido' => 30, 'Loop Master' => 60]
        ];
        
        $charCosts = $costs[$character->getType()] ?? [];
        
        while (true) {
            echo "\nFORMAS DISPONIVEIS:\n";
            foreach ($forms as $idx => $form) {
                $cost = $charCosts[$form] ?? 0;
                $current = ($form === $character->getCurrentForm()) ? " [ATUAL]" : "";
                $canUse = $character->getCurrentEnergy() >= $cost ? "✓" : "✗";
                echo " [" . ($idx + 1) . "]$canUse $form (Custo: $cost EN)$current\n";
            }
            echo "[0]VOLTAR\nOpcao: ";
            
            $choice = $this->readChar();
            
            if ($choice === '0') {
                echo YELLOW . "Voltando ao menu de ação...\n" . RESET;
                return; // Volta para showActionMenu
            }
            
            if (is_numeric($choice) && $choice >= 1 && $choice <= count($forms)) {
                $formName = $forms[$choice - 1];
                
                // Verifica energia ANTES de mostrar ASCII
                if ($character->getCurrentEnergy() >= $charCosts[$formName]) {
                    // Mostra ASCII da forma sem limpar tela
                    $formKey = strtolower($character->getType()) . '_' . strtolower(str_replace([' ', 'ç', 'ã'], ['_', 'c', 'a'], $formName));
                    // Remove duplicação do nome do personagem no início
                    $formKey = strtolower($character->getType()) . '_' . str_replace(strtolower($character->getType()) . '_', '', strtolower(str_replace([' ', 'ç', 'ã'], ['_', 'c', 'a'], $formName)));
                    $ascii = ASCIIArts::getASCII($formKey);
                    if ($ascii) {
                        echo "\n" . $ascii . "\n";
                        sleep(2);
                        // Não limpa tela para manter menu visível entre jogadores
                    }
                } else {
                    echo RED . BOLD . "⚡ ENERGIA INSUFICIENTE! Você precisa de " . $charCosts[$formName] . " EN para usar " . $formName . "\n" . RESET;
                    sleep(1);
                    continue; // Continua no loop de formas
                }
                
                if ($character->applyForm($formName)) {
                    $this->playerForms[$player - 1] = $formName;
                    $character->useEnergy($charCosts[$formName]);
                    $this->executeAction($player, 'transform');
                    return; // Executa ação e termina turno
                } else {
                    echo RED . "FALHA AO TROCAR DE FORMA!\n" . RESET;
                    sleep(1);
                    continue; // Continua no loop de formas
                }
            } else {
                echo RED . BOLD . "❌ OPCAO INVALIDA! Digite 1-" . count($forms) . " ou 0\n" . RESET;
                sleep(1);
            }
        }
    }
    
    private function showSpecialMoves($player, $character) {
        $moves = $this->getMovesForCharacter($character->getType());
        
        while (true) {
            echo "\nHABILIDADES ESPECIAIS:\n";
            $idx = 1;
            foreach ($moves as $key => $move) {
                // Verificação especial para Mugetsu
                if ($key === 'mugetsu' && $character->getType() === 'Ichigo') {
                    $canUse = (method_exists($character, 'canUseMugetsu') && $character->canUseMugetsu()) ? "✓" : "✗";
                    $hpPercent = ($character->getCurrentHp() / $character->getMaxHp()) * 100;
                    echo sprintf("[%d]%s %s (HP: %.0f%%) ", $idx, $canUse, $move['name'], $hpPercent);
                } else {
                    // Verifica se Mugetsu já foi usado (bloqueia outras habilidades)
                    if ($character->getType() === 'Ichigo' && method_exists($character, 'hasUsedMugetsu') && $character->hasUsedMugetsu()) {
                        $canUse = "✗";
                        echo sprintf("[%d]%s %s (BLOQUEADO) ", $idx, $canUse, $move['name']);
                    } else {
                        $canUse = $character->getCurrentEnergy() >= $move['cost'] ? "✓" : "✗";
                        echo sprintf("[%d]%s %s (%d EN) ", $idx, $canUse, $move['name'], $move['cost']);
                    }
                }
                $idx++;
            }
            echo "[0]VOLTAR\nOpcao: ";
            
            $choice = $this->readChar();
            
            if ($choice === '0') {
                echo YELLOW . "Voltando ao menu de ação...\n" . RESET;
                return;
            }
            
            if (is_numeric($choice) && $choice >= 1 && $choice <= count($moves)) {
                $moveKeys = array_keys($moves);
                $selectedMove = $moveKeys[$choice - 1];
                
                // Verifica energia e condições especiais ANTES de mostrar ASCII
                $canUse = true;
                $errorMessage = "";
                
                // Verificação especial para Mugetsu
                if ($selectedMove === 'mugetsu' && $character->getType() === 'Ichigo') {
                    if (!method_exists($character, 'canUseMugetsu') || !$character->canUseMugetsu()) {
                        $canUse = false;
                        $errorMessage = RED . BOLD . "⚠️ MUGETSU só pode ser usado com 10% ou menos de HP!" . RESET;
                    }
                } else {
                    // Verificação normal de energia para outras habilidades
                    if ($character->getCurrentEnergy() < $moves[$selectedMove]['cost']) {
                        $canUse = false;
                        $errorMessage = RED . BOLD . "⚡ ENERGIA INSUFICIENTE! Você precisa de " . $moves[$selectedMove]['cost'] . " EN para usar " . $moves[$selectedMove]['name'] . RESET;
                    }
                }
                
                if ($canUse) {
                    // Mostra ASCII art sem limpar tela (mantém menu visível)
                    $ascii = ASCIIArts::getASCII($selectedMove);
                    if ($ascii) {
                        echo "\n" . $ascii . "\n";
                        sleep(2);
                        // Não limpa tela para manter menu visível entre jogadores
                    }
                    
                    $this->executeAction($player, $selectedMove);
                    return; // Executa ação e termina turno
                } else {
                    // Erro - mostra mensagem e NÃO executa a ação
                    echo $errorMessage . "\n";
                    sleep(1);
                    continue; // Volta para o menu sem executar ação
                }
            } else {
                echo RED . BOLD . "❌ OPCAO INVALIDA! Digite 1-" . count($moves) . " ou 0\n" . RESET;
                sleep(1);
            }
        }
    }
    
    private function getMovesForCharacter($type) {
        $moves = [
            'Sukuna' => [
                'cleave' => ['name' => 'CLEAVE', 'cost' => 30, 
                            'desc' => 'Ataque com sangramento continuo'],
                'dismantle' => ['name' => 'DISMANTLE', 'cost' => 40,
                               'desc' => 'Multiplos cortes destrutivos'],
                'shrine' => ['name' => 'MALEVOLENT SHRINE', 'cost' => 80,
                            'desc' => 'Dano continuo por 3 turnos']
            ],
            'Goku' => [
                'kamehameha' => ['name' => 'KAMEHAMEHA', 'cost' => 40,
                                'desc' => 'Onda de energia lendaria'],
                'genkidama' => ['name' => 'GENKIDAMA', 'cost' => 60,
                               'desc' => 'Bomba cosmica'],
                'teleport' => ['name' => 'Teleporte', 'cost' => 70,
                              'desc' => 'Esquiva com contraataque']
            ],
            'Naruto' => [
                'rasengan' => ['name' => 'RASENGAN', 'cost' => 35,
                              'desc' => 'Esfera giratoria de chi'],
                'rasenshuriken' => ['name' => 'RASENSHURIKEN', 'cost' => 55,
                                   'desc' => 'Shuriken de ar com multiplos golpes'],
                'kurama' => ['name' => 'MODO KURAMA', 'cost' => 75,
                            'desc' => 'Poder maximo da raposa']
            ],
            'Ichigo' => [
                'getsuga' => ['name' => 'GETSUGA TENSHO', 'cost' => 40,
                             'desc' => 'Ataque lunar classico'],
                'bankai' => ['name' => 'BANKAI', 'cost' => 60,
                            'desc' => 'Forma final da Zanpakuto'],
                'hollow' => ['name' => 'HOLLOW FORM', 'cost' => 50,
                            'desc' => 'Transformacao sinistra'],
                'mugetsu' => ['name' => 'MUGETSU', 'cost' => 0,
                             'desc' => 'Ataque final (apenas com 10% HP)']
            ],
            'Subaru' => [
                'whitewhale' => ['name' => 'WHITE WHALE', 'cost' => 50,
                                'desc' => 'Poder do Leviata Branco'],
                'returnbydeath' => ['name' => 'RETURN BY DEATH', 'cost' => 90,
                                   'desc' => 'Volta no tempo e restaura HP'],
                'packed_lunch' => ['name' => 'PACKED LUNCH', 'cost' => 35,
                                  'desc' => 'Ataque com a mochila']
            ]
        ];
        return $moves[$type] ?? [];
    }
    
    private function showDetailedStatus($player, $character) {
        echo "\n" . ($player === 1 ? GREEN : BLUE) . BOLD . $this->playerNames[$player - 1] . RESET . "\n";
        echo str_repeat("=", 60) . "\n";
        echo sprintf("Personagem: %s (%s)\n", $character->getType(), $this->playerForms[$player - 1]);
        echo sprintf("HP: %d/%d | Energia: %d/%d\n", $character->getCurrentHp(), $character->getMaxHp(), 
                     $character->getCurrentEnergy(), $character->getMaxEnergy());
        echo sprintf("ATK: %d | DEF: %d | SPD: %d\n\n", $character->getAttack(), $character->getDefense(), $character->getSpeed());
        
        if (!empty($this->effects[$player])) {
            echo "Efeitos: " . MAGENTA . implode(", ", $this->effects[$player]) . RESET . "\n\n";
        }
    }
    
    private function showBattleEnd() {
        $state = $this->battleManager->getState();
        $winner = $state['player1']['character']->getCurrentHp() > 0 ? 1 : 2;
        $winnerChar = ($winner === 1) ? $state['player1']['character'] : $state['player2']['character'];
        
        echo ($winner === 1 ? GREEN : BLUE) . BOLD . "
╔════════════════════════════════════════════════════════════════╗
║                                                                ║
║                      BATALHA FINALIZADA!                       ║
║                                                                ║
║  VENCEDOR: " . str_pad(strtoupper($this->playerNames[$winner - 1]), 47) . "║
║                                                                ║
╚════════════════════════════════════════════════════════════════╝
        " . RESET . "\n\n";
        
        // Mostra ASCII do personagem vencedor
        $characterKey = strtolower($winnerChar->getType()) . '_character';
        $ascii = ASCIIArts::getASCII($characterKey);
        if ($ascii) {
            echo ($winner === 1 ? GREEN : BLUE) . BOLD . "PERSONAGEM VENCEDOR:\n" . RESET;
            echo $ascii . "\n\n";
        }
        
        echo YELLOW . BOLD . "ESTADISTICAS FINAIS\n" . RESET;
        echo str_repeat("=", 60) . "\n\n";
        
        for ($p = 1; $p <= 2; $p++) {
            $pData = ($p === 1) ? $state['player1'] : $state['player2'];
            $char = $pData['character'];
            $color = ($p === 1) ? GREEN : BLUE;
            
            echo $color . BOLD . $this->playerNames[$p - 1] . " (" . 
                 $char->getType() . " - " . $this->playerForms[$p - 1] . ")" . RESET . "\n";
            echo sprintf("  HP Final: %d/%d | Turnos: %d\n\n", $char->getCurrentHp(), $char->getMaxHp(), $this->turn);
        }
        
        echo str_repeat("=", 60) . "\n";
        echo "Pressione ENTER para voltar ao menu...";
        fgets(STDIN);
    }
}

try {
    new TerminalBattle();
} catch (Exception $e) {
    echo RED . "ERRO: " . $e->getMessage() . RESET . "\n";
    exit(1);
}
