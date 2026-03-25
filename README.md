# 🎮 BATTLE ARENA - TERMINAL v2.0

Um jogo de batalha estratégica em tempo real no terminal, com personagens de anime, efeitos especiais e sistema de transformações.

✅ **Sistema de Formas** - Cada personagem pode mudar de forma
✅ **Efeito de Bleed** - Sukuna causa sangramento contínuo no ataque Cleave
✅ **Dano Contínuo** - Expansão de Domínio causa dano por turnos
✅ **ASCII Arts Integradas** - Ver animações ao usar habilidades especiais

## 🎯 COMO JOGAR

```bash
./play.sh
```

Ou diretamente:
```bash
php battle-cli.php
```

## 🎭 PERSONAGENS COM FORMAS

### 👹 SUKUNA
- **Forma Normal** - Equilibrado
- **ERA HEIAN** - Ataque máximo
- **Habilidades:** Cleave (sangramento), Dismantle, Malevolent Shrine (dano contínuo)

### 🧑‍🚀 GOKU
- **Forma Normal** - Velocista
- **Super Saiyajin** - ATK + DEF
- **Ultra Instinto** - Máxima velocidade
- **Habilidades:** Kamehameha, Genkidama, Ultra Instinto

### 🥷 NARUTO
- **Forma Normal** - Balanceado
- **Modo Kurama** - Força + Resistência
- **Sábio dos Seis Caminhos** - Poder máximo
- **Habilidades:** Rasengan, Rasenshuriken, Modo Kurama

### ⚔️ ICHIGO
- **Forma Normal** - Equilibrado
- **Bankai** - Poder liberado
- **Hollow Ichigo** - Forma sinistra
- **Habilidades:** Getsuga Tensho, Bankai, Hollow Form
    

## 💥 SISTEMA DE EFEITOS

### SANGRAMENTO (Sukuna - Cleave)
- Causa 5% dano do HP máximo por turno
- Dura enquanto ativo

### EXPANSÃO DE DOMÍNIO (Sukuna - Shrine)
- Causa 8% dano do HP máximo por turno
- Efeito devastador

## 🕹️ AÇÕES POR TURNO

1. **ATACAR** - Ataque básico
2. **DEFENDER** - Reduz dano recebido
3. **HABILIDADES ESPECIAIS** - 3 movimentos únicos por personagem
4. **MUDAR FORMA** - Transforma o personagem
5. **VER STATUS** - Mostra estatísticas detalhadas

## 📊 ESTATÍSTICAS

- **HP** - Pontos de vida (0 = derrota)
- **ENERGIA** - Recurso para habilidades
- **ATAQUE** - Dano causado
- **DEFESA** - Reduz dano recebido
- **VELOCIDADE** - Ordem de ação

## 🎨 CORES ANSI

- Verde: Player 1
- Azul: Player 2
- Amarelo: Menus
- Vermelho: Efeitos negativos
- Magenta: Eventos especiais

## 📁 ESTRUTURA

```
battle-arena/
├── battle-cli.php            (🎮 Jogo principal)
├── play.sh                   (Launcher)
├── classes/                  (Personagens PHP)
│   ├── Character.php
│   ├── Sukuna.php
│   ├── Goku.php
│   ├── Naruto.php
│   ├── Ichigo.php
│   └── Subaru.php (NOVO)
├── managers/                 (Lógica de batalha)
│   └── BattleManager.php
└── README.md                 (Este arquivo)
```

## 🛠️ CUSTOMIZAÇÕES

### Adicionar novo personagem

1. Criar classe em `classes/NomePersonagem.php`
2. Estender `Character`
3. Adicionar à lista de personagens em `battle-cli.php`

### Modificar cores

Edite as constantes no topo de `battle-cli.php`:
```php
const RED = "\033[91m";
const GREEN = "\033[92m";
```

### Alterar dano de efeitos

No método `applyOngoingEffects()`:
```php
$damage = $enemy->getMaxHp() * 0.05; // 5% = sangramento
$damage = $enemy->getMaxHp() * 0.08; // 8% = domínio
```

## 🐛 REQUISITOS

- PHP 7.4+
- Terminal com suporte a ANSI colors
- Linux/Mac/WSL

## 📝 NOTAS

- ASCII arts estão integradas no código
- Cada personagem tem 3 habilidades únicas
- Sistema de formas oferece estratégia diversa
- Efeitos funcionam em tempo real

---

**Criado por:** Battle Arena Dev
**Última atualização:** 11 de Mar de 2026
**Versão:** 2.0 - Final
