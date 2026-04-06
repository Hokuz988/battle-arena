<?php
/**
 * GameView - Interface Visual Geral do Jogo
 * Contém todas as visualizações básicas e constantes
 */

class GameView {
    // Cores ANSI
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
    
    /**
     * Limpa a tela
     */
    public function clearScreen() {
        system('clear');
    }
    
    /**
     * Mostra tela de boas-vindas
     */
    public function showWelcome() {
        $this->clearScreen();
        echo self::CYAN . self::BOLD . "
╔════════════════════════════════════════════════════════════════╗
║                   BATTLE ARENA TERMINAL v4.0                   ║
║              Anime Fighters - Strategy Battle Game              ║
║                Powered by JavaScript Database                   ║
║  Escolha seus personagens e lutem em épicas batalhas!          ║
╚════════════════════════════════════════════════════════════════╝
        " . self::RESET . "\n";
    }
    
    /**
     * Mostra mensagem de despedida
     */
    public function showGoodbye() {
        $this->clearScreen();
        echo self::GREEN . self::BOLD . "Obrigado por jogar Battle Arena!\n" . self::RESET;
    }
    
    /**
     * Mostra mensagem de erro
     */
    public function showError($message) {
        echo self::RED . self::BOLD . $message . self::RESET . "\n";
    }
    
    /**
     * Mostra mensagem de sucesso
     */
    public function showSuccess($message) {
        echo self::GREEN . self::BOLD . $message . self::RESET . "\n";
    }
    
    /**
     * Mostra mensagem informativa
     */
    public function showInfo($message) {
        echo self::BLUE . self::BOLD . $message . self::RESET . "\n";
    }
    
    /**
     * Cria uma barra de progresso visual
     */
    public function drawBar($percent, $width, $color = null) {
        $filled = intval(($percent / 100) * $width);
        $empty = $width - $filled;
        
        // Define cor baseada no percentual se não especificada
        if ($color === null) {
            if ($percent > 50) {
                $barColor = self::GREEN;
            } elseif ($percent > 20) {
                $barColor = self::YELLOW;
            } else {
                $barColor = self::RED;
            }
        } else {
            $barColor = $color;
        }
        
        $filledChar = "▓";
        $emptyChar = "░";
        
        $bar = $barColor . "╠" . str_repeat($filledChar, $filled) . str_repeat($emptyChar, $empty) . "╣" . self::RESET;
        
        // Adiciona percentual formatado
        $percentText = sprintf("%3.0f%%", $percent);
        $percentColor = ($percent > 50) ? self::GREEN : (($percent > 20) ? self::YELLOW : self::RED);
        
        return $bar . " " . $percentColor . $percentText . self::RESET;
    }
    
    /**
     * Mostra cabeçalho com título
     */
    public function showHeader($title, $subtitle = "") {
        $this->clearScreen();
        echo self::CYAN . self::BOLD . "
╔════════════════════════════════════════════════════════════════╗
║" . str_pad(" " . $title, 63, " ", STR_PAD_RIGHT) . "║";
        
        if (!empty($subtitle)) {
            echo "║" . str_pad(" " . $subtitle, 63, " ", STR_PAD_RIGHT) . "║";
        }
        
        echo "╚════════════════════════════════════════════════════════════════╝
        " . self::RESET . "\n";
    }
    
    /**
     * Exibe o menu principal
     */
    public function showMainMenu() {
        echo GameView::YELLOW . GameView::BOLD . "\n MENU PRINCIPAL\n" . GameView::RESET;
        echo str_repeat("=", 60) . "\n\n";
        echo "  [1] BATALHAR\n";
        echo "  [2] VER RANKING\n";
        echo "  [0] SAIR\n\n";
        echo "Opcao: ";
    }
    
    /**
     * Mostra separador
     */
    public function showSeparator($char = "=", $length = 60) {
        echo str_repeat($char, $length) . "\n";
    }
    
    /**
     * Mostra mensagem de aguardar
     */
    public function showWaiting($message = "Pressione ENTER para continuar...") {
        echo "\n" . self::YELLOW . $message . self::RESET;
        fgets(STDIN);
    }
    
    /**
     * Mostra animação de carregamento
     */
    public function showLoading($message = "Carregando", $duration = 2) {
        echo self::YELLOW . $message;
        
        for ($i = 0; $i < $duration * 3; $i++) {
            echo ".";
            usleep(333000);
        }
        
        echo self::RESET . "\n";
    }
}
