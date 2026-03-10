<?php
// index.php - Battle Arena
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Carrega todas as classes
require_once 'config/Database.php';
require_once 'interfaces/ActionInterface.php';
require_once 'classes/Character.php';
require_once 'classes/Sukuna.php';
require_once 'classes/Goku.php';
require_once 'classes/Naruto.php';
require_once 'classes/Ichigo.php';
require_once 'managers/BattleManager.php';
require_once 'managers/BattleState.php';

// Inicia sessão
session_start();

// PROCESSA REQUISIÇÕES AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch($_POST['action']) {
        case 'start_battle':
            // Validação
            if (empty($_POST['p1_name']) || empty($_POST['p2_name']) || 
                empty($_POST['p1_char']) || empty($_POST['p2_char'])) {
                echo json_encode(['status' => 'error', 'message' => 'Dados incompletos']);
                exit;
            }
            
            // Cria nova batalha
            $battle = new BattleManager(
                $_POST['p1_name'],
                $_POST['p2_name'],
                $_POST['p1_char'],
                $_POST['p2_char']
            );
            
            // Salva estado na sessão
            $_SESSION['battle_state'] = new BattleState($battle);
            $_SESSION['gameOver'] = false;
            
            echo json_encode(['status' => 'started']);
            exit;
            
        case 'player_action':
            if (!isset($_SESSION['battle_state'])) {
                echo json_encode(['error' => 'Batalha não iniciada']);
                exit;
            }
            
            // Validação
            if (empty($_POST['player']) || empty($_POST['move'])) {
                echo json_encode(['error' => 'Dados inválidos']);
                exit;
            }
            
            // Recria battle manager do estado
            $state = $_SESSION['battle_state'];
            $battle = new BattleManager(
                $state->player1_name,
                $state->player2_name,
                $state->character1_type,
                $state->character2_type
            );
            $battle->restoreFromState($state);
            
            // Processa ação - retorna array com resultados do turno
            $turnResult = $battle->setAction((int)$_POST['player'], $_POST['move']);
            
            // Atualiza estado
            $_SESSION['battle_state'] = new BattleState($battle);
            
            // Coleta todos os resultados de animação do turno
            $animations = [];
            if (isset($turnResult['actions']) && is_array($turnResult['actions'])) {
                foreach ($turnResult['actions'] as $action) {
                    $animations[] = [
                        'player' => $action['player'] ?? null,
                        'animation' => $action['animation'] ?? 'attack',
                        'damage' => round($action['damage'] ?? 0, 2),
                        'message' => $action['message'] ?? ''
                    ];
                }
            }
            
            // Verifica fim do jogo
            if (isset($turnResult['gameOver']) && $turnResult['gameOver']) {
                $_SESSION['gameOver'] = true;
                
                // Salva no banco de dados
                try {
                    $db = Database::getInstance();
                    $finalState = $battle->getState();
                    $winner = $turnResult['winner'] == 1 ? $finalState['player1']['name'] : $finalState['player2']['name'];
                    
                    $db->saveBattle([
                        'player1' => $finalState['player1']['name'],
                        'player2' => $finalState['player2']['name'],
                        'char1' => $finalState['player1']['character']->getType(),
                        'char2' => $finalState['player2']['character']->getType(),
                        'winner' => $winner,
                        'turns' => $finalState['turn'],
                        'log' => $finalState['log']
                    ]);
                } catch (Exception $e) {
                    error_log("Erro ao salvar: " . $e->getMessage());
                }
            }
            
            echo json_encode([
                'status' => count($animations) > 0 ? 'turncompleted' : 'waiting',
                'animations' => $animations,
                'gameOver' => isset($turnResult['gameOver']) ? $turnResult['gameOver'] : false,
                'winner' => $turnResult['winner'] ?? null
            ]);
            exit;
            
        case 'get_state':
            if (!isset($_SESSION['battle_state'])) {
                echo json_encode(['error' => 'Batalha não iniciada']);
                exit;
            }
            
            // Recria battle manager
            $state = $_SESSION['battle_state'];
            $battle = new BattleManager(
                $state->player1_name,
                $state->player2_name,
                $state->character1_type,
                $state->character2_type
            );
            $battle->restoreFromState($state);
            
            // Retorna estado formatado
            $battleState = $battle->getState();
            echo json_encode([
                'turn' => $battleState['turn'],
                'player1' => [
                    'name' => $battleState['player1']['name'],
                    'hp' => $battleState['player1']['character']->getCurrentHp(),
                    'maxHp' => $battleState['player1']['character']->getMaxHp(),
                    'energy' => $battleState['player1']['character']->getCurrentEnergy(),
                    'maxEnergy' => $battleState['player1']['character']->getMaxEnergy(),
                    'character' => $battleState['player1']['character']->getType(),
                    'effects' => $battleState['player1']['character']->getEffects()
                ],
                'player2' => [
                    'name' => $battleState['player2']['name'],
                    'hp' => $battleState['player2']['character']->getCurrentHp(),
                    'maxHp' => $battleState['player2']['character']->getMaxHp(),
                    'energy' => $battleState['player2']['character']->getCurrentEnergy(),
                    'maxEnergy' => $battleState['player2']['character']->getMaxEnergy(),
                    'character' => $battleState['player2']['character']->getType(),
                    'effects' => $battleState['player2']['character']->getEffects()
                ],
                'log' => $battleState['log'],
                'gameOver' => $battleState['gameOver']
            ]);
            exit;
            
        case 'reset':
            session_destroy();
            echo json_encode(['status' => 'reset']);
            exit;
    }
}

// Pega ranking e personagens
$ranking = [];
$characters = [];
try {
    $db = Database::getInstance();
    $ranking = $db->getRanking();
} catch (Exception $e) {
    error_log("Erro no ranking: " . $e->getMessage());
}

try {
    $db = Database::getInstance();
    $characters = $db->getAllCharacters();
} catch (Exception $e) {
    error_log("Erro ao carregar personagens: " . $e->getMessage());
}

// Verifica se há batalha ativa
$battleActive = isset($_SESSION['battle_state']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battle Arena - Anime Fighters</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="game-container">
        <!-- Tela de Seleção -->
        <div id="selectionScreen" class="screen <?= $battleActive ? 'hidden' : '' ?>">
            <h1>⚔️ BATTLE ARENA ⚔️</h1>
            
            <div class="player-selection">
                <div class="player-section">
                    <h2>Jogador 1</h2>
                    <input type="text" id="p1Name" placeholder="Seu nome" value="Player 1">
                    
                    <div class="character-grid">
                        <?php foreach ($characters as $char): ?>
                            <div class="character-card" data-char="<?= htmlspecialchars($char['type']) ?>" data-player="1">
                                <div class="character-preview sprite-<?= htmlspecialchars($char['type']) ?>"></div>
                                <h3><?= htmlspecialchars($char['name']) ?></h3>
                                <div class="stats">
                                    <span>HP: <?= (int)$char['hp'] ?></span>
                                    <span>ATK: <?= (int)$char['attack'] ?></span>
                                    <span>DEF: <?= (int)$char['defense'] ?></span>
                                    <span>SPD: <?= (int)$char['speed'] ?></span>
                                </div>
                                <p class="special-desc">Personagem disponível na Battle Arena</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="player-section">
                    <h2>Jogador 2</h2>
                    <input type="text" id="p2Name" placeholder="Seu nome" value="Player 2">
                    
                    <div class="character-grid">
                        <?php foreach ($characters as $char): ?>
                            <div class="character-card" data-char="<?= htmlspecialchars($char['type']) ?>" data-player="2">
                                <div class="character-preview sprite-<?= htmlspecialchars($char['type']) ?>"></div>
                                <h3><?= htmlspecialchars($char['name']) ?></h3>
                                <div class="stats">
                                    <span>HP: <?= (int)$char['hp'] ?></span>
                                    <span>ATK: <?= (int)$char['attack'] ?></span>
                                    <span>DEF: <?= (int)$char['defense'] ?></span>
                                    <span>SPD: <?= (int)$char['speed'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <button id="startBattle" class="start-btn" onclick="startBattle()">INICIAR BATALHA!</button>
            </div>
            
            <!-- Ranking -->
            <div class="ranking-section">
                <h2>🏆 RANKING 🏆</h2>
                <table>
                    <tr>
                        <th>Jogador</th>
                        <th>Batalhas</th>
                        <th>Vitórias</th>
                    </tr>
                    <?php if ($ranking && count($ranking) > 0): ?>
                        <?php foreach($ranking as $rank): ?>
                        <tr>
                            <td><?= htmlspecialchars($rank['player']) ?></td>
                            <td><?= $rank['battles'] ?></td>
                            <td><?= $rank['wins'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">Nenhuma batalha registrada ainda</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        
        <!-- Tela de Batalha -->
        <div id="battleScreen" class="screen <?= $battleActive ? '' : 'hidden' ?>">
            <div class="battle-arena">
                <div class="arena-background"></div>
                
                <div class="character-container">
                    <!-- Personagem 1 -->
                    <div class="character character-left" id="char1">
                        <div class="character-sprite" id="sprite1"></div>
                        
                        <div class="status-bars">
                            <h3 id="p1NameDisplay"></h3>
                            <h4 id="p1Type"></h4>
                            
                            <div class="hp-bar">
                                <div class="hp-fill" id="p1HpFill" style="width: 100%"></div>
                                <span class="hp-text" id="p1HpText"></span>
                            </div>
                            
                            <div class="energy-bar">
                                <div class="energy-fill" id="p1EnergyFill" style="width: 100%"></div>
                                <span class="energy-text" id="p1EnergyText"></span>
                            </div>
                            
                            <div class="effects" id="p1Effects"></div>
                        </div>
                    </div>
                    
                    <!-- Personagem 2 -->
                    <div class="character character-right" id="char2">
                        <div class="character-sprite" id="sprite2"></div>
                        
                        <div class="status-bars">
                            <h3 id="p2NameDisplay"></h3>
                            <h4 id="p2Type"></h4>
                            
                            <div class="hp-bar">
                                <div class="hp-fill" id="p2HpFill" style="width: 100%"></div>
                                <span class="hp-text" id="p2HpText"></span>
                            </div>
                            
                            <div class="energy-bar">
                                <div class="energy-fill" id="p2EnergyFill" style="width: 100%"></div>
                                <span class="energy-text" id="p2EnergyText"></span>
                            </div>
                            
                            <div class="effects" id="p2Effects"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Menu de Ações -->
                <div class="action-menu" id="actionMenu">
                    <button class="action-btn" onclick="backToSelection()" style="position: absolute; top: 10px; right: 10px; padding: 8px 12px; font-size: 10px;">← VOLTAR</button>
                    <h3>Vez do <span id="currentPlayer">Jogador 1</span></h3>
                    
                    <div class="actions" id="actionButtons">
                        <button class="action-btn attack" onclick="selectAction('attack')">⚔️ ATACAR</button>
                        <button class="action-btn defend" onclick="selectAction('defend')">🛡️ DEFENDER</button>
                    </div>
                    
                    <div id="specialMovesContainer" style="margin-top: 15px;">
                        <h4 style="color: #333; margin-bottom: 10px; text-align: center;">HABILIDADES ESPECIAIS</h4>
                        <div id="specialMoves" class="special-moves"></div>
                    </div>
                </div>
                
                <!-- Log -->
                <div class="battle-log" id="battleLog"></div>
            </div>
        </div>
        
        <!-- Tela de Resultado -->
        <div id="resultScreen" class="screen hidden">
            <div class="result-container">
                <h1 id="winnerMessage"></h1>
                
                <div class="stats-container" id="battleStats"></div>
                
                <button class="start-btn" onclick="resetGame()">NOVA BATALHA</button>
            </div>
        </div>
    </div>
    
    <script>
        // Variáveis globais
        let currentPlayer = 1; 
        let player1Ready = false;
        let player2Ready = false;
        let selectedChars = { player1: null, player2: null };
        let battleActive = <?= $battleActive ? 'true' : 'false' ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>