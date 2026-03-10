// ============================================
// POKÉMON FIRE RED BATTLE SYSTEM
// ============================================

let battleUpdateInterval = null;
let currentAnimations = new Set();

// Handler global para erros
window.addEventListener('error', function(event) {
    console.error('ERRO GLOBAL:', event.error);
});

// ============================================
// INICIALIZAÇÃO DO JOGO
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema Pokémon FireRed iniciado');
    
    // Inicializa sprites se já houver batalha ativa
    if (typeof battleActive !== 'undefined' && battleActive) {
        console.log('Batalha ativa detectada, iniciando atualizações...');
        setTimeout(() => {
            startBattleUpdate();
        }, 100);
    }
    
    // Seleção de personagens
    const cards = document.querySelectorAll('.character-card');
    cards.forEach(card => {
        card.addEventListener('click', function() {
            const player = this.dataset.player;
            const char = this.dataset.char;
            
            // Remove seleção anterior
            document.querySelectorAll(`.character-card[data-player="${player}"]`).forEach(c => {
                c.classList.remove('selected');
            });
            
            // Adiciona nova seleção
            this.classList.add('selected');
            
            // Atualiza variáveis globais (capitalize name)
            const charCapitalized = char.charAt(0).toUpperCase() + char.slice(1);
            if (player === '1') {
                selectedChars.player1 = charCapitalized;
                player1Ready = true;
            } else {
                selectedChars.player2 = charCapitalized;
                player2Ready = true;
            }
            
            // Habilita botão start se ambos prontos
            if (player1Ready && player2Ready) {
                document.querySelector('.start-btn').disabled = false;
            }
        });
    });
    
    // Botão start
    const startBtn = document.querySelector('.start-btn');
    if (startBtn) {
        startBtn.addEventListener('click', startBattle);
    }
    
    // Verifica se há batalha ativa
    if (typeof battleActive !== 'undefined' && battleActive) {
        showBattleScreen();
        startBattleUpdate();
    }
});

// ============================================
// FUNÇÕES PRINCIPAIS
// ============================================

function startBattle() {
    const p1Name = document.querySelector('#p1Name').value || 'Player 1';
    const p2Name = document.querySelector('#p2Name').value || 'Player 2';
    
    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'start_battle',
            p1_name: p1Name,
            p2_name: p2Name,
            p1_char: selectedChars.player1,
            p2_char: selectedChars.player2
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Resposta startBattle:', data);
        if (data.status === 'started') {
            showBattleScreen();
            startBattleUpdate();
        } else {
            alert('Erro ao iniciar batalha: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de comunicação com o servidor');
    });
}

function showBattleScreen() {
    console.log('Mostrando tela de batalha');
    document.getElementById('selectionScreen').classList.add('hidden');
    document.getElementById('battleScreen').classList.remove('hidden');
    
    // Força atualização imediata dos sprites e carrega estado
    setTimeout(() => {
        forceUpdateSprites();
        updateBattleState();
    }, 50);
}

function forceUpdateSprites() {
    // Obtém personagens selecionados
    const p1Char = selectedChars.player1;
    const p2Char = selectedChars.player2;
    
    console.log('Forçando atualização:', { p1Char, p2Char });
    
    // Atualiza sprite 1
    if (p1Char) {
        const sprite1 = document.getElementById('sprite1');
        if (sprite1) {
            const characterEmojis = {
                'Sukuna': '👹',
                'Goku': '🧑‍🚀',
                'Naruto': '🥷',
                'Ichigo': '⚔️'
            };
            sprite1.textContent = characterEmojis[p1Char] || '❓';
            sprite1.className = 'character-sprite';
            console.log(`Sprite 1 atualizado: ${p1Char} -> ${sprite1.textContent}`);
        }
    }
    
    // Atualiza sprite 2
    if (p2Char) {
        const sprite2 = document.getElementById('sprite2');
        if (sprite2) {
            const characterEmojis = {
                'Sukuna': '👹',
                'Goku': '🧑‍🚀',
                'Naruto': '🥷',
                'Ichigo': '⚔️'
            };
            sprite2.textContent = characterEmojis[p2Char] || '❓';
            sprite2.className = 'character-sprite';
            console.log(`Sprite 2 atualizado: ${p2Char} -> ${sprite2.textContent}`);
        }
    }
}

function startBattleUpdate() {
    if (battleUpdateInterval) {
        clearInterval(battleUpdateInterval);
    }
    
    battleUpdateInterval = setInterval(updateBattleState, 1000);
}

function updateBattleState() {
    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'get_state'
        })
    })
    .then(response => response.json())
    .then(data => {
        // Se recebeu turn, é um estado válido
        if (data.turn !== undefined) {
            updateUI(data);
        } else if (data.error) {
            console.error('Erro:', data.error);
            if (battleUpdateInterval) {
                clearInterval(battleUpdateInterval);
                battleUpdateInterval = null;
            }
        }
    })
    .catch(error => {
        console.error('Erro de rede:', error);
    });
}

function updateUI(battleData) {
    console.log('Atualizando UI com dados:', battleData);
    
    // Alterna player baseado no turno
    if (battleData.turn !== undefined) {
        currentPlayer = (battleData.turn % 2 === 1) ? 1 : 2;
        const playerDisplay = document.getElementById('currentPlayer');
        if (playerDisplay) playerDisplay.textContent = `Jogador ${currentPlayer}`;
    }
    
    // Força atualização dos sprites primeiro
    if (battleData.player1 && battleData.player1.character) {
        updateCharacterSprite(1, battleData.player1.character);
        updateStatusBars(1, battleData.player1);
        updateEffects(1, battleData.player1.effects || []);
    }
    
    if (battleData.player2 && battleData.player2.character) {
        updateCharacterSprite(2, battleData.player2.character);
        updateStatusBars(2, battleData.player2);
        updateEffects(2, battleData.player2.effects || []);
    }
    
    // Atualiza botões
    if (battleData.player1) {
        updateActionButtons(currentPlayer, battleData.player1.character);
    }
    
    // Atualiza log
    if (battleData.log) {
        updateBattleLog(battleData.log);
    }
    
    // Verifica fim da batalha
    if (battleData.winner) {
        endBattle(battleData);
    }
}

// ============================================
// FUNÇÕES DE PERSONAGENS
// ============================================

function updateCharacterSprite(player, type) {
    const sprite = document.getElementById(`sprite${player}`);
    if (!sprite) {
        console.error(`Sprite ${player} não encontrado`);
        return;
    }
    
    // Mapeamento de personagens para emojis
    const characterEmojis = {
        'Sukuna': '👹',
        'Goku': '🧑‍🚀',
        'Naruto': '🥷',
        'Ichigo': '⚔️'
    };
    
    sprite.textContent = characterEmojis[type] || '❓';
    sprite.className = `character-sprite sprite-${type.toLowerCase()}`;
    
    console.log(`Sprite ${player} atualizado: ${type} -> ${sprite.textContent}`);
}

function updateStatusBars(player, playerData) {
    const hpFill = document.querySelector(`#p${player}HpFill`);
    const energyFill = document.querySelector(`#p${player}EnergyFill`);
    const hpText = document.querySelector(`#p${player}HpText`);
    const energyText = document.querySelector(`#p${player}EnergyText`);
    const nameDisplay = document.querySelector(`#p${player}NameDisplay`);
    const typeDisplay = document.querySelector(`#p${player}Type`);
    
    if (!hpFill || !energyFill || !hpText || !energyText || !nameDisplay || !typeDisplay) {
        console.error(`Elementos de status do player ${player} não encontrados`);
        return;
    }
    
    if (nameDisplay) {
        nameDisplay.textContent = playerData.name;
    }
    
    if (typeDisplay) {
        typeDisplay.textContent = playerData.character;
    }
    
    if (hpFill) {
        const hpPercent = (playerData.hp / playerData.maxHp) * 100;
        hpFill.style.width = hpPercent + '%';
        
        // Remove classes de status
        hpFill.classList.remove('low', 'critical');
        
        // Adiciona classe baseada no HP
        if (hpPercent <= 25) {
            hpFill.classList.add('critical');
        } else if (hpPercent <= 50) {
            hpFill.classList.add('low');
        }
    }
    
    if (energyFill) {
        const energyPercent = (playerData.energy / playerData.maxEnergy) * 100;
        energyFill.style.width = energyPercent + '%';
    }
    
    if (hpText) {
        hpText.textContent = `${playerData.hp}/${playerData.maxHp}`;
    }
    
    if (energyText) {
        energyText.textContent = `${playerData.energy}/${playerData.maxEnergy}`;
    }
}

function updateEffects(player, effects) {
    const effectsContainer = document.querySelector(`#p${player}Effects`);
    if (!effectsContainer) return;
    
    effectsContainer.innerHTML = '';
    
    effects.forEach(effect => {
        const effectDiv = document.createElement('div');
        effectDiv.className = 'effect-icon';
        effectDiv.textContent = getEffectIcon(effect);
        effectDiv.title = effect;
        effectsContainer.appendChild(effectDiv);
    });
}

function getEffectIcon(effect) {
    const icons = {
        'bleeding': '🩸',
        'boost': '⬆️',
        'stun': '😵',
        'poison': '☠️',
        'burn': '🔥',
        'freeze': '❄️',
        'confusion': '🌀',
        'weakness': '⬇️'
    };
    return icons[effect] || '✨';
}

// ============================================
// FUNÇÕES DE AÇÃO
// ============================================

function selectAction(action) {
    console.log('Ação selecionada:', action);
    
    // Verifica se é a vez do jogador atual
    if (typeof currentPlayer === 'undefined') {
        console.error('currentPlayer não definido');
        return;
    }
    
    // Desabilita botões durante a ação
    const buttons = document.querySelectorAll('.action-btn');
    buttons.forEach(btn => btn.disabled = true);
    
    // Envia ação para o servidor
    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'player_action',
            player: currentPlayer,
            move: action
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Resposta do servidor:', data);
        
        if (data.status && (data.status === 'turncompleted' || data.status === 'waiting')) {
            // Executa animações
            if (data.animations && data.animations.length > 0) {
                data.animations.forEach(animData => {
                    playPokemonAnimation(animData);
                });
            }
            
            // Atualiza UI após animação
            setTimeout(() => {
                updateBattleState();
            }, 500);
            
            // Verifica fim do jogo
            if (data.gameOver) {
                endBattle(data);
            }
        } else {
            console.error('Erro na ação:', data.error || 'Erro desconhecido');
            alert('Erro na ação: ' + (data.error || 'Erro desconhecido'));
            
            // Reabilita botões em caso de erro
            buttons.forEach(btn => btn.disabled = false);
        }
    })
    .catch(error => {
        console.error('Erro de comunicação:', error);
        alert('Erro de comunicação com o servidor');
        
        // Reabilita botões em caso de erro
        buttons.forEach(btn => btn.disabled = false);
    });
}

function updateActionButtons(currentPlayer, character) {
    const actionBtns = document.querySelectorAll('.action-btn');
    const specialMovesDiv = document.getElementById('specialMoves');
    
    // Habilita/desabilita botões baseado no jogador atual
    actionBtns.forEach(btn => {
        btn.disabled = false; // Habilita todos por enquanto
    });
    
    // Atualiza golpes especiais
    if (specialMovesDiv && character) {
        updateSpecialButtons(character);
    }
}

function updateSpecialButtons(character) {
    const specialDiv = document.getElementById('specialMoves');
    if (!specialDiv) return;
    
    specialDiv.innerHTML = '';
    
    const movesByType = {
        'Sukuna': [
            { key: 'cleave', name: 'Cleave', cost: 30 },
            { key: 'dismantle', name: 'Dismantle', cost: 40 },
            { key: 'shrine', name: 'Shrine', cost: 80 }
        ],
        'Goku': [
            { key: 'kamehameha', name: 'Kamehameha', cost: 40 },
            { key: 'genkidama', name: 'Genkidama', cost: 60 },
            { key: 'teleport', name: 'Teleport', cost: 70 }
        ],
        'Naruto': [
            { key: 'rasengan', name: 'Rasengan', cost: 35 },
            { key: 'rasenshuriken', name: 'Rasenshuriken', cost: 55 },
            { key: 'kurama', name: 'Kurama', cost: 75 }
        ],
        'Ichigo': [
            { key: 'getsuga', name: 'Getsuga', cost: 40 },
            { key: 'bankai', name: 'Bankai', cost: 60 },
            { key: 'hollow', name: 'Hollow', cost: 50 }
        ]
    };
    
    const moves = movesByType[character] || [];
    
    moves.forEach(move => {
        const btn = document.createElement('button');
        btn.className = 'action-btn';
        btn.textContent = `${move.name} (${move.cost})`;
        btn.dataset.move = move.key;
        btn.dataset.cost = move.cost;
        btn.dataset.player = currentPlayer;
        btn.onclick = () => performAction(move.key);
        specialDiv.appendChild(btn);
    });
}

function updateBattleLog(log) {
    const logContainer = document.querySelector('.battle-log');
    if (!logContainer) return;
    
    logContainer.innerHTML = '';
    
    // Mostra apenas as últimas 8 entradas
    const recentLog = log.slice(-8);
    
    recentLog.forEach(entry => {
        const logDiv = document.createElement('div');
        logDiv.className = `log-entry ${entry.type}`;
        logDiv.textContent = entry.message;
        logContainer.appendChild(logDiv);
    });
    
    // Auto-scroll para o final
    logContainer.scrollTop = logContainer.scrollHeight;
}

// ============================================
// SISTEMA DE ANIMAÇÕES POKÉMON
// ============================================

function performAction(action) {
    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'player_action',
            player: currentPlayer,
            move: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status && (data.status === 'turncompleted' || data.status === 'waiting')) {
            // Executa animações
            if (data.animations && data.animations.length > 0) {
                data.animations.forEach(animData => {
                    playPokemonAnimation(animData);
                });
            }
            
            // Atualiza UI após animação
            setTimeout(() => {
                updateBattleState();
            }, 500);
            
            // Verifica fim do jogo
            if (data.gameOver) {
                endBattle(data);
            }
        } else {
            alert('Erro na ação: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de comunicação');
    });
}

function playPokemonAnimation(actionData) {
    const attacker = actionData.player;
    const target = actionData.target || (attacker === 1 ? 2 : 1);
    const animationType = actionData.animation || 'attack';
    
    const attackerSprite = document.getElementById(`sprite${attacker}`);
    const targetSprite = document.getElementById(`sprite${target}`);
    
    if (!attackerSprite || !targetSprite) return;
    
    // Limpa animações anteriores
    clearAnimations(attackerSprite);
    clearAnimations(targetSprite);
    
    // Executa animação baseada no tipo
    switch (animationType) {
        case 'attack':
            playAttackAnimation(attackerSprite, targetSprite);
            break;
        case 'defend':
            playDefendAnimation(attackerSprite);
            break;
        case 'kamehameha':
            playSpecialAnimation(attackerSprite, targetSprite, 'kamehameha');
            break;
        case 'rasengan':
            playSpecialAnimation(attackerSprite, targetSprite, 'rasengan');
            break;
        case 'getsuga':
            playSpecialAnimation(attackerSprite, targetSprite, 'getsuga');
            break;
        case 'cleave':
            playSpecialAnimation(attackerSprite, targetSprite, 'cleave');
            break;
        default:
            playAttackAnimation(attackerSprite, targetSprite);
    }
    
    // Mostra dano se houver
    if (actionData.damage > 0) {
        setTimeout(() => {
            showDamageNumber(targetSprite, actionData.damage, actionData.critical);
        }, 300);
    }
}

function playAttackAnimation(attacker, target) {
    // Animação de ataque simples
    attacker.classList.add('sprite-attacking');
    
    setTimeout(() => {
        target.classList.add('sprite-hit');
        
        setTimeout(() => {
            clearAnimations(attacker);
            clearAnimations(target);
        }, 200);
    }, 200);
}

function playDefendAnimation(defender) {
    defender.classList.add('sprite-defending');
    
    setTimeout(() => {
        clearAnimations(defender);
    }, 300);
}

function playSpecialAnimation(attacker, target, type) {
    // Animação de ataque especial
    attacker.classList.add('sprite-attacking');
    
    // Cria projétil
    createPokemonProjectile(attacker, target, type);
    
    setTimeout(() => {
        target.classList.add('sprite-hit');
        
        setTimeout(() => {
            clearAnimations(attacker);
            clearAnimations(target);
        }, 200);
    }, 400);
}

function createPokemonProjectile(attacker, target, type) {
    const projectile = document.createElement('div');
    projectile.className = 'pokemon-projectile';
    
    // Define emoji baseado no tipo
    const projectileEmojis = {
        'kamehameha': '⚡',
        'rasengan': '🌀',
        'getsuga': '🌙',
        'cleave': '⚔️'
    };
    
    projectile.textContent = projectileEmojis[type] || '⚡';
    projectile.style.cssText = `
        position: absolute;
        font-size: 30px;
        z-index: 1000;
        pointer-events: none;
        transition: all 0.4s ease-out;
    `;
    
    // Posição inicial
    const attackerRect = attacker.getBoundingClientRect();
    const targetRect = target.getBoundingClientRect();
    const container = document.querySelector('.character-container');
    
    projectile.style.left = attackerRect.left + 'px';
    projectile.style.top = attackerRect.top + 'px';
    
    container.appendChild(projectile);
    
    // Anima até o alvo
    setTimeout(() => {
        projectile.style.left = targetRect.left + 'px';
        projectile.style.top = targetRect.top + 'px';
        projectile.style.opacity = '0';
        projectile.style.transform = 'scale(1.5)';
    }, 50);
    
    // Remove projétil
    setTimeout(() => {
        projectile.remove();
    }, 450);
}

function clearAnimations(element) {
    const animationClasses = [
        'sprite-attacking', 'sprite-hit', 'sprite-defending'
    ];
    
    animationClasses.forEach(className => {
        element.classList.remove(className);
    });
}

function showDamageNumber(targetSprite, damage, isCritical = false) {
    const damageText = document.createElement('div');
    damageText.className = 'damage-number' + (isCritical ? ' critical' : '');
    damageText.textContent = Math.round(damage);
    
    // Posição relativa ao sprite
    const targetRect = targetSprite.getBoundingClientRect();
    const container = document.querySelector('.character-container');
    
    damageText.style.cssText = `
        position: absolute;
        left: ${targetRect.left + targetRect.width / 2}px;
        top: ${targetRect.top}px;
        transform: translate(-50%, -50%);
        z-index: 1001;
        animation: damage-float 1s ease-out forwards;
    `;
    
    // Adiciona animação CSS se não existir
    if (!document.querySelector('#damage-animations')) {
        const style = document.createElement('style');
        style.id = 'damage-animations';
        style.textContent = `
            @keyframes damage-float {
                0% {
                    transform: translate(-50%, -50%) translateY(0) scale(1);
                    opacity: 1;
                }
                100% {
                    transform: translate(-50%, -50%) translateY(-60px) scale(0.5);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    container.appendChild(damageText);
    
    // Remove após animação
    setTimeout(() => {
        damageText.remove();
    }, 1000);
}

// ============================================
// FUNÇÕES DE FIM DE BATALHA
// ============================================

function endBattle(battleData) {
    if (battleUpdateInterval) {
        clearInterval(battleUpdateInterval);
    }
    
    // Mostra tela de resultado
    const resultContainer = document.createElement('div');
    resultContainer.className = 'result-container';
    resultContainer.innerHTML = `
        <h2 id="winnerMessage">${battleData.winner} VENCEU!</h2>
        <div class="stats-container">
            <div class="stat-row">
                <div class="stat-box">
                    <h3>${battleData.player1.name}</h3>
                    <div class="stat-item">Dano: ${battleData.player1.stats.damage_dealt}</div>
                    <div class="stat-item">Recebido: ${battleData.player1.stats.damage_taken}</div>
                    <div class="stat-item">Ataques: ${battleData.player1.stats.attacks}</div>
                </div>
                <div class="stat-box">
                    <h3>${battleData.player2.name}</h3>
                    <div class="stat-item">Dano: ${battleData.player2.stats.damage_dealt}</div>
                    <div class="stat-item">Recebido: ${battleData.player2.stats.damage_taken}</div>
                    <div class="stat-item">Ataques: ${battleData.player2.stats.attacks}</div>
                </div>
            </div>
        </div>
        <button class="reset-btn" onclick="resetGame()">NOVA BATALHA</button>
    `;
    
    document.getElementById('battleScreen').appendChild(resultContainer);
}

function backToSelection() {
    console.log('Voltando para tela de seleção');
    
    // Para o intervalo de atualização
    if (battleUpdateInterval) {
        clearInterval(battleUpdateInterval);
        battleUpdateInterval = null;
    }
    
    // Reseta variáveis
    currentPlayer = 1;
    player1Ready = false;
    player2Ready = false;
    selectedChars = { player1: null, player2: null };
    battleActive = false;
    
    // Mostra tela de seleção
    document.getElementById('battleScreen').classList.add('hidden');
    document.getElementById('selectionScreen').classList.remove('hidden');
    
    // Limpa sprites
    const sprite1 = document.getElementById('sprite1');
    const sprite2 = document.getElementById('sprite2');
    if (sprite1) {
        sprite1.textContent = '';
        sprite1.className = 'character-sprite';
    }
    if (sprite2) {
        sprite2.textContent = '';
        sprite2.className = 'character-sprite';
    }
}

function resetGame() {
    console.log('Resetando jogo...');
    
    // Para o intervalo de atualização
    if (battleUpdateInterval) {
        clearInterval(battleUpdateInterval);
        battleUpdateInterval = null;
    }
    
    // Reseta variáveis globais
    currentPlayer = 1;
    player1Ready = false;
    player2Ready = false;
    selectedChars = { player1: null, player2: null };
    battleActive = false;
    
    // Limpa sprites
    const sprite1 = document.getElementById('sprite1');
    const sprite2 = document.getElementById('sprite2');
    if (sprite1) {
        sprite1.textContent = '';
        sprite1.className = 'character-sprite';
    }
    if (sprite2) {
        sprite2.textContent = '';
        sprite2.className = 'character-sprite';
    }
    
    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'reset'
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Resposta reset:', data);
        if (data.status === 'reset') {
            // Recarrega a página para limpar tudo
            location.reload();
        } else {
            alert('Erro ao resetar: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro no reset:', error);
        alert('Erro de comunicação');
    });
}
