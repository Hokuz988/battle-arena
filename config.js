// ============================================
// POKÉMON FIRE RED CONFIGURATION
// ============================================

const POKEMON_CONFIG = {
    // Configurações visuais
    VISUAL: {
        // Cores do tema
        colors: {
            primary: '#ffcc00',
            secondary: '#ff9900',
            success: '#4CAF50',
            danger: '#F44336',
            warning: '#FF9800',
            info: '#2196F3',
            dark: '#000000',
            light: '#ffffff',
            background: '#f8f8f8'
        },
        
        // Gradientes de fundo
        backgrounds: {
            battle: 'linear-gradient(to bottom, #87CEEB 0%, #E0F6FF 50%, #90EE90 100%)',
            selection: 'linear-gradient(to bottom, #f8f8f8 0%, #e9ecef 100%)',
            menu: 'linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%)'
        },
        
        // Tamanhos
        sizes: {
            sprite: 96,
            spriteSmall: 64,
            buttonHeight: 40,
            borderRadius: 8,
            borderWidth: 2
        },
        
        // Animações
        animations: {
            duration: {
                fast: 200,
                normal: 400,
                slow: 800
            },
            easing: {
                smooth: 'ease-out',
                bounce: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
                linear: 'linear'
            }
        }
    },
    
    // Configurações de personagens
    CHARACTERS: {
        'Sukuna': {
            emoji: '👹',
            color: '#8B0000',
            bgGradient: 'linear-gradient(135deg, #8B0000, #DC143C)',
            moves: [
                { key: 'attack', name: 'Attack', cost: 10, power: 15 },
                { key: 'defend', name: 'Defend', cost: 5, power: 0 },
                { key: 'cleave', name: 'Cleave', cost: 30, power: 35 },
                { key: 'dismantle', name: 'Dismantle', cost: 25, power: 30 },
                { key: 'shrine', name: 'Shrine', cost: 40, power: 45 }
            ]
        },
        'Goku': {
            emoji: '🧑‍🚀',
            color: '#FF6B35',
            bgGradient: 'linear-gradient(135deg, #FF6B35, #FFD700)',
            moves: [
                { key: 'attack', name: 'Attack', cost: 10, power: 15 },
                { key: 'defend', name: 'Defend', cost: 5, power: 0 },
                { key: 'kamehameha', name: 'Kamehameha', cost: 35, power: 40 },
                { key: 'genkidama', name: 'Genkidama', cost: 50, power: 60 },
                { key: 'teleport', name: 'Teleport', cost: 20, power: 0 }
            ]
        },
        'Naruto': {
            emoji: '🥷',
            color: '#FF6600',
            bgGradient: 'linear-gradient(135deg, #FF6600, #FFA500)',
            moves: [
                { key: 'attack', name: 'Attack', cost: 10, power: 15 },
                { key: 'defend', name: 'Defend', cost: 5, power: 0 },
                { key: 'rasengan', name: 'Rasengan', cost: 30, power: 35 },
                { key: 'rasenshuriken', name: 'Rasenshuriken', cost: 40, power: 45 },
                { key: 'kurama', name: 'Kurama', cost: 45, power: 50 }
            ]
        },
        'Ichigo': {
            emoji: '⚔️',
            color: '#1E90FF',
            bgGradient: 'linear-gradient(135deg, #1E90FF, #4169E1)',
            moves: [
                { key: 'attack', name: 'Attack', cost: 10, power: 15 },
                { key: 'defend', name: 'Defend', cost: 5, power: 0 },
                { key: 'getsuga', name: 'Getsuga', cost: 35, power: 40 },
                { key: 'bankai', name: 'Bankai', cost: 50, power: 55 },
                { key: 'hollow', name: 'Hollow', cost: 40, power: 45 }
            ]
        }
    },
    
    // Configurações de efeitos
    EFFECTS: {
        particles: {
            sparkle: { color: '#ffcc00', size: 4, count: 8 },
            heal: { color: '#4CAF50', size: 6, count: 6 },
            damage: { color: '#F44336', size: 6, count: 12 },
            critical: { color: '#FF9800', size: 8, count: 16 }
        },
        
        projectiles: {
            kamehameha: { emoji: '⚡', color: '#FFD700', size: 30 },
            rasengan: { emoji: '🌀', color: '#00BFFF', size: 28 },
            getsuga: { emoji: '🌙', color: '#C0C0C0', size: 26 },
            cleave: { emoji: '⚔️', color: '#FF4500', size: 24 }
        },
        
        sounds: {
            enabled: true,
            volume: 0.7,
            files: {
                attack: 'sounds/attack.wav',
                hit: 'sounds/hit.wav',
                critical: 'sounds/critical.wav',
                heal: 'sounds/heal.wav',
                special: 'sounds/special.wav',
                victory: 'sounds/victory.wav',
                defeat: 'sounds/defeat.wav'
            }
        }
    },
    
    // Configurações de jogo
    GAME: {
        // Valores base
        baseStats: {
            hp: 100,
            energy: 50,
            attack: 15,
            defense: 10,
            speed: 10
        },
        
        // Regras
        rules: {
            maxEnergy: 100,
            energyRegen: 10,
            criticalChance: 0.1,
            criticalMultiplier: 2.0,
            missChance: 0.05
        },
        
        // Tempo
        timing: {
            battleUpdate: 1000,
            animationDuration: 500,
            messageDelay: 2000,
            resultDelay: 3000
        }
    },
    
    // Configurações de UI
    UI: {
        // Layout
        layout: {
            headerHeight: 60,
            footerHeight: 80,
            sidebarWidth: 200,
            padding: 20
        },
        
        // Responsividade
        breakpoints: {
            mobile: 768,
            tablet: 1024,
            desktop: 1200
        },
        
        // Fontes
        fonts: {
            primary: "'Courier New', monospace",
            secondary: "Arial, sans-serif",
            pokemon: "'Press Start 2P', cursive"
        }
    },
    
    // Configurações de debug
    DEBUG: {
        enabled: false,
        showLogs: true,
        showFPS: false,
        showHitboxes: false,
        godMode: false
    }
};

// Funções utilitárias
const POKEMON_UTILS = {
    // Obtém configuração de personagem
    getCharacterConfig(name) {
        return POKEMON_CONFIG.CHARACTERS[name] || null;
    },
    
    // Obtém cor do tema
    getColor(name) {
        return POKEMON_CONFIG.VISUAL.colors[name] || '#000000';
    },
    
    // Formata número
    formatNumber(num) {
        return Math.round(num).toString();
    },
    
    // Calcula dano
    calculateDamage(attack, defense, critical = false) {
        let damage = Math.max(1, attack - defense / 2);
        
        if (critical) {
            damage *= POKEMON_CONFIG.GAME.rules.criticalMultiplier;
        }
        
        return Math.round(damage);
    },
    
    // Verifica crítico
    isCritical() {
        return Math.random() < POKEMON_CONFIG.GAME.rules.criticalChance;
    },
    
    // Verifica erro
    isMiss() {
        return Math.random() < POKEMON_CONFIG.GAME.rules.missChance;
    },
    
    // Aplica cor baseada no HP
    getHPColor(current, max) {
        const percentage = (current / max) * 100;
        
        if (percentage <= 25) {
            return POKEMON_CONFIG.VISUAL.colors.danger;
        } else if (percentage <= 50) {
            return POKEMON_CONFIG.VISUAL.colors.warning;
        } else {
            return POKEMON_CONFIG.VISUAL.colors.success;
        }
    },
    
    // Log de debug
    log(message, type = 'info') {
        if (POKEMON_CONFIG.DEBUG.enabled && POKEMON_CONFIG.DEBUG.showLogs) {
            console.log(`[POKEMON DEBUG] ${type.toUpperCase()}: ${message}`);
        }
    }
};

// Exporta configurações
window.POKEMON_CONFIG = POKEMON_CONFIG;
window.POKEMON_UTILS = POKEMON_UTILS;
