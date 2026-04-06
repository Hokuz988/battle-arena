<?php
/**
 * InputService - Serviço de Entrada de Dados
 * Centraliza todo o sistema de input do terminal
 */

class InputService {
    
    /**
     * Lê um caractere único sem precisar de Enter
     */
    public function readChar() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows: PowerShell
            $input = shell_exec('powershell -Command "$Host.UI.RawUI.ReadKey(\'NoEcho,IncludeKeyDown\')"');
            if ($input) {
                $char = trim(explode(':', $input)[1] ?? '');
                return $char;
            }
        } else {
            // Linux/Unix: stty
            $stty_mode = shell_exec('stty -g');
            shell_exec('stty -icanon -echo');
            $input = fread(STDIN, 1);
            shell_exec('stty ' . $stty_mode);
            
            if ($input !== false && $input !== '') {
                return $input;
            }
        }
        
        // Fallback
        if (function_exists('readline')) {
            $input = readline();
            return trim($input);
        }
        
        return trim(fgets(STDIN));
    }
    
    /**
     * Lê uma linha completa (com Enter)
     */
    public function readLine($prompt = "") {
        echo $prompt;
        if (function_exists('readline')) {
            return trim(readline());
        }
        return trim(fgets(STDIN));
    }
    
    /**
     * Lê entrada com validação
     */
    public function readWithValidation($prompt, $validOptions, $caseSensitive = false) {
        while (true) {
            echo $prompt;
            $input = $this->readChar();
            
            if (!$caseSensitive) {
                $input = strtolower($input);
                $validOptions = array_map('strtolower', $validOptions);
            }
            
            if (in_array($input, $validOptions)) {
                return $input;
            }
            
            echo "\033[91mOpção inválida! Tente novamente.\033[0m\n";
        }
    }
    
    /**
     * Lê número com validação de range
     */
    public function readNumber($prompt, $min = 1, $max = 9) {
        while (true) {
            echo $prompt;
            $input = $this->readChar();
            
            if (is_numeric($input) && $input >= $min && $input <= $max) {
                return (int)$input;
            }
            
            echo "\033[91mDigite um número entre $min e $max!\033[0m\n";
        }
    }
    
    /**
     * Lê texto com opção de padrão
     */
    public function readText($prompt, $default = "") {
        $input = $this->readLine($prompt);
        return empty($input) ? $default : $input;
    }
    
    /**
     * Pausa esperando Enter
     */
    public function pause($message = "Pressione ENTER para continuar...") {
        echo "\n" . $message;
        fgets(STDIN);
    }
    
    /**
     * Confirmação (S/N)
     */
    public function confirm($message = "Confirmar? (S/N): ") {
        $choice = $this->readWithValidation($message, ['s', 'n'], false);
        return $choice === 's';
    }
}
