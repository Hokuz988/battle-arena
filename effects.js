// ============================================
// POKÉMON FIRE RED EFFECTS SYSTEM
// ============================================

class PokemonEffects {
    constructor() {
        this.audioEnabled = true;
        this.particleContainer = null;
        this.init();
    }
    
    init() {
        // Cria container para partículas
        this.particleContainer = document.createElement('div');
        this.particleContainer.id = 'particle-container';
        this.particleContainer.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        `;
        document.body.appendChild(this.particleContainer);
        
        // Cria estilos para partículas
        this.createParticleStyles();
    }
    
    createParticleStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes particle-float {
                0% {
                    transform: translateY(0) scale(1);
                    opacity: 1;
                }
                100% {
                    transform: translateY(-100px) scale(0);
                    opacity: 0;
                }
            }
            
            .particle {
                position: absolute;
                pointer-events: none;
                animation: particle-float 1s ease-out forwards;
            }
            
            .sparkle {
                width: 4px;
                height: 4px;
                background: #ffcc00;
                border-radius: 50%;
                box-shadow: 0 0 6px #ffcc00;
            }
            
            .star {
                color: #ffcc00;
                font-size: 16px;
            }
            
            .heal-particle {
                width: 6px;
                height: 6px;
                background: #4CAF50;
                border-radius: 50%;
                box-shadow: 0 0 8px #4CAF50;
            }
            
            .damage-particle {
                width: 6px;
                height: 6px;
                background: #F44336;
                border-radius: 50%;
                box-shadow: 0 0 8px #F44336;
            }
        `;
        document.head.appendChild(style);
    }
    
    // Cria partículas ao redor de um elemento
    createParticles(element, type = 'sparkle', count = 8) {
        const rect = element.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        
        for (let i = 0; i < count; i++) {
            const particle = document.createElement('div');
            particle.className = `particle ${type}`;
            
            // Posição circular ao redor do elemento
            const angle = (Math.PI * 2 * i) / count;
            const radius = 50;
            const x = centerX + Math.cos(angle) * radius;
            const y = centerY + Math.sin(angle) * radius;
            
            particle.style.left = x + 'px';
            particle.style.top = y + 'px';
            
            this.particleContainer.appendChild(particle);
            
            // Remove partícula após animação
            setTimeout(() => particle.remove(), 1000);
        }
    }
    
    // Cria explosão de partículas
    createExplosion(x, y, type = 'damage', count = 12) {
        for (let i = 0; i < count; i++) {
            const particle = document.createElement('div');
            particle.className = `particle ${type}-particle`;
            
            // Direção aleatória
            const angle = Math.random() * Math.PI * 2;
            const velocity = 100 + Math.random() * 100;
            const vx = Math.cos(angle) * velocity;
            const vy = Math.sin(angle) * velocity;
            
            particle.style.left = x + 'px';
            particle.style.top = y + 'px';
            
            // Animação de explosão
            particle.style.animation = 'none';
            particle.style.transition = 'all 0.6s ease-out';
            
            this.particleContainer.appendChild(particle);
            
            // Aplica movimento
            setTimeout(() => {
                particle.style.transform = `translate(${vx}px, ${vy}px) scale(0)`;
                particle.style.opacity = '0';
            }, 10);
            
            // Remove partícula
            setTimeout(() => particle.remove(), 600);
        }
    }
    
    // Efeito de cura
    playHealEffect(element) {
        this.createParticles(element, 'heal-particle', 6);
        
        // Cria cruz de cura
        const heal = document.createElement('div');
        heal.textContent = '✚';
        heal.style.cssText = `
            position: absolute;
            color: #4CAF50;
            font-size: 24px;
            font-weight: bold;
            z-index: 1000;
            animation: heal-float 1s ease-out forwards;
        `;
        
        const rect = element.getBoundingClientRect();
        heal.style.left = (rect.left + rect.width / 2) + 'px';
        heal.style.top = (rect.top + rect.height / 2) + 'px';
        heal.style.transform = 'translate(-50%, -50%)';
        
        this.particleContainer.appendChild(heal);
        setTimeout(() => heal.remove(), 1000);
    }
    
    // Efeito de level up
    playLevelUpEffect(element) {
        this.createParticles(element, 'star', 12);
        
        const levelUp = document.createElement('div');
        levelUp.textContent = 'LEVEL UP!';
        levelUp.style.cssText = `
            position: absolute;
            color: #ffcc00;
            font-size: 32px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
            z-index: 1000;
            animation: level-up-bounce 1.5s ease-out forwards;
        `;
        
        const rect = element.getBoundingClientRect();
        levelUp.style.left = (rect.left + rect.width / 2) + 'px';
        levelUp.style.top = (rect.top + rect.height / 2) + 'px';
        levelUp.style.transform = 'translate(-50%, -50%)';
        
        this.particleContainer.appendChild(levelUp);
        setTimeout(() => levelUp.remove(), 1500);
    }
    
    // Efeito de status
    playStatusEffect(element, status) {
        const statusEmojis = {
            'burn': '🔥',
            'freeze': '❄️',
            'poison': '☠️',
            'stun': '😵',
            'confusion': '🌀'
        };
        
        const emoji = statusEmojis[status] || '✨';
        const statusElement = document.createElement('div');
        statusElement.textContent = emoji;
        statusElement.style.cssText = `
            position: absolute;
            font-size: 20px;
            z-index: 1000;
            animation: status-pulse 0.8s ease-out forwards;
        `;
        
        const rect = element.getBoundingClientRect();
        statusElement.style.left = (rect.left + rect.width / 2) + 'px';
        statusElement.style.top = (rect.top - 20) + 'px';
        statusElement.style.transform = 'translate(-50%, -50%)';
        
        this.particleContainer.appendChild(statusElement);
        setTimeout(() => statusElement.remove(), 800);
    }
    
    // Toca som (se disponível)
    playSound(type) {
        if (!this.audioEnabled) return;
        
        // Aqui você pode adicionar sons reais
        // Por enquanto, apenas log no console
        console.log(`Playing sound: ${type}`);
    }
    
    // Limpa todas as partículas
    clearAll() {
        this.particleContainer.innerHTML = '';
    }
}

// Adiciona estilos adicionais
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
    @keyframes heal-float {
        0% {
            transform: translate(-50%, -50%) translateY(0) scale(1);
            opacity: 1;
        }
        100% {
            transform: translate(-50%, -50%) translateY(-40px) scale(1.5);
            opacity: 0;
        }
    }
    
    @keyframes level-up-bounce {
        0% {
            transform: translate(-50%, -50%) scale(0) rotate(0deg);
            opacity: 0;
        }
        50% {
            transform: translate(-50%, -50%) scale(1.2) rotate(180deg);
            opacity: 1;
        }
        100% {
            transform: translate(-50%, -50%) scale(1) translateY(-60px) rotate(360deg);
            opacity: 0;
        }
    }
    
    @keyframes status-pulse {
        0% {
            transform: translate(-50%, -50%) scale(0);
            opacity: 0;
        }
        50% {
            transform: translate(-50%, -50%) scale(1.2);
            opacity: 1;
        }
        100% {
            transform: translate(-50%, -50%) scale(0.8);
            opacity: 0;
        }
    }
`;
document.head.appendChild(additionalStyles);

// Inicializa o sistema de efeitos
const pokemonEffects = new PokemonEffects();
