<?php
/**
 * AnimationService - Sistema de Animações e ASCII Arts
 * Centraliza todos os elementos visuais e animações
 */

class AnimationService {
    private static $asciiArts = null;
    
    public function __construct() {
        if (self::$asciiArts === null) {
            $this->loadASCIIArts();
        }
    }
    
    /**
     * Carrega o arquivo de ASCII arts
     */
    private function loadASCIIArts() {
        $asciiFile = __DIR__ . '/../../classes/ASCIIArts.php';
        
        if (file_exists($asciiFile)) {
            require_once $asciiFile;
            self::$asciiArts = ASCIIArts::$arts;
        } else {
            self::$asciiArts = [];
        }
    }
    
    /**
     * Obtém ASCII art de personagem
     */
    public function getCharacterASCII($characterId) {
        $key = strtolower($characterId) . '_character';
        return self::$asciiArts[$key] ?? null;
    }
    
    /**
     * Obtém ASCII art de habilidade
     */
    public function getAbilityASCII($abilityId) {
        return self::$asciiArts[$abilityId] ?? null;
    }
    
    /**
     * Obtém ASCII art de forma
     */
    public function getFormASCII($formId) {
        // Mapear IDs de forma para chaves ASCII
        $formMapping = [
            'heian_era' => 'sukuna_heian_era',
            'super_saiyajin' => 'goku_super_saiyajin',
            'ultra_instinto' => 'goku_ultra_instinto',
            'modo_kurama' => 'naruto_modo_kurama',
            'sabio' => 'naruto_sabio_dos_seis_caminhos',
            'bankai' => 'ichigo_bankai',
            'arrancar' => 'ichigo_arrancar'
        ];
        
        $key = $formMapping[$formId] ?? $formId;
        return self::$asciiArts[$key] ?? null;
    }
    
    /**
     * Mostra animação de Black Flash
     */
    public function showBlackFlash() {
        $animation = [
            "⚡",
            "⚡⚡",
            "⚡⚡⚡",
            "⚡⚡⚡⚡",
            "💥 BLACK FLASH 💥",
            "⚡⚡⚡⚡",
            "⚡⚡⚡",
            "⚡⚡",
            "⚡"
        ];
        
        foreach ($animation as $frame) {
            system('clear');
            echo "\033[1;33m" . $frame . "\033[0m\n";
            usleep(150000);
        }
        
        system('clear');
    }
    
    /**
     * Mostra animação de esquiva
     */
    public function showDodgeAnimation() {
        $ascii = self::$asciiArts['dodge'] ?? null;
        if ($ascii) {
            system('clear');
            echo "\n" . $ascii . "\n";
            sleep(2);
            system('clear');
        }
    }
    
    /**
     * Mostra animação de ataque
     */
    public function showAttack($attackType = 'normal') {
        $animations = [
            'normal' => ["⚔️", "💥", "🗡️"],
            'special' => ["✨", "⚡", "🔥"],
            'ultimate' => ["💫", "☄️", "🌟"]
        ];
        
        $frames = $animations[$attackType] ?? $animations['normal'];
        
        foreach ($frames as $frame) {
            echo "\r" . $frame . " ";
            usleep(200000);
        }
        
        echo "\n";
    }
    
    /**
     * Mostra barra de carregamento animada
     */
    public function showLoadingBar($message = "Carregando", $duration = 2) {
        $chars = ["|", "/", "-", "\\"];
        $steps = $duration * 10;
        
        echo $message . " ";
        
        for ($i = 0; $i < $steps; $i++) {
            $char = $chars[$i % 4];
            echo "\r" . $message . " " . $char . " " . round(($i / $steps) * 100) . "%";
            usleep(100000);
        }
        
        echo "\r" . $message . " ✓ 100%\n";
    }
    
    /**
     * Mostra efeito de dano
     */
    public function showDamageEffect($damage, $critical = false) {
        $prefix = $critical ? "⚡ CRÍTICO! " : "";
        $color = $critical ? "\033[95m" : "\033[91m";
        $reset = "\033[0m";
        
        echo $color . $prefix . "-{$damage} HP" . $reset . "\n";
        
        // Efeito visual
        for ($i = 0; $i < 3; $i++) {
            echo "\r💔 ";
            usleep(150000);
        }
        echo "\r   \n";
    }
    
    /**
     * Mostra efeito de cura
     */
    public function showHealEffect($healing) {
        $color = "\033[92m";
        $reset = "\033[0m";
        
        echo $color . "+{$healing} HP" . $reset . "\n";
        
        // Efeito visual
        for ($i = 0; $i < 3; $i++) {
            echo "\r💚 ";
            usleep(150000);
        }
        echo "\r   \n";
    }
    
    /**
     * Mostra efeito de energia
     */
    public function showEnergyEffect($energy, $isCost = true) {
        $color = $isCost ? "\033[93m" : "\033[96m";
        $reset = "\033[0m";
        $prefix = $isCost ? "-" : "+";
        
        echo $color . $prefix . "{$energy} EN" . $reset . "\n";
        
        // Efeito visual
        for ($i = 0; $i < 2; $i++) {
            echo "\r⚡ ";
            usleep(150000);
        }
        echo "\r   \n";
    }
    
    /**
     * Mostra tela de vitória
     */
    public function showVictoryScreen($winnerName, $characterType) {
        $victoryASCII = "
╔════════════════════════════════════════════════════════════════╗
║                                                                ║
║                      🏆 VITÓRIA! 🏆                           ║
║                                                                ║
║  " . str_pad("VENCEDOR: " . strtoupper($winnerName), 47) . "║
║                                                                ║
╚════════════════════════════════════════════════════════════════╝
        ";
        
        echo "\033[92m" . $victoryASCII . "\033[0m\n\n";
        
        $characterASCII = $this->getCharacterASCII($characterType);
        if ($characterASCII) {
            echo $characterASCII . "\n\n";
        }
    }
    
    /**
     * Mostra tela de derrota
     */
    public function showDefeatScreen() {
        $defeatASCII = "
╔════════════════════════════════════════════════════════════════╗
║                                                                ║
║                      💀 DERROTA 💀                            ║
║                                                                ║
║  " . str_pad("Você foi derrotado!", 47) . "║
║                                                                ║
║  " . str_pad("Tente novamente!", 47) . "║
║                                                                ║
╚════════════════════════════════════════════════════════════════╝
        ";
        
        echo "\033[91m" . $defeatASCII . "\033[0m\n\n";
    }
    
    /**
     * Mostra personagens lado a lado
     */
    public function showBattleScene($character1, $character2) {
        $ascii1 = $this->getCharacterASCII(strtolower($character1->getType()));
        $ascii2 = $this->getCharacterASCII(strtolower($character2->getType()));
        
        if ($ascii1 && $ascii2) {
            echo "\n" . GameView::GREEN . $ascii1 . GameView::RESET;
            echo "\n" . str_repeat(" ", 30) . "VS\n";
            echo "\n" . GameView::BLUE . $ascii2 . GameView::RESET . "\n\n";
        }
    }
}

?>
