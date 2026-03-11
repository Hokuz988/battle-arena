<?php
// classes/AudioManager.php

class AudioManager {
    private static $enabled = true;
    private static $audioPlayer = null;
    
    public static function init() {
        // Detecta qual reprodutor de áudio está disponível
        if (self::commandExists('ffplay')) {
            self::$audioPlayer = 'ffplay';
        } elseif (self::commandExists('aplay')) {
            self::$audioPlayer = 'aplay';
        } elseif (self::commandExists('paplay')) {
            self::$audioPlayer = 'paplay';
        } elseif (self::commandExists('mpg123')) {
            self::$audioPlayer = 'mpg123';
        } else {
            self::$enabled = false;
            return false;
        }
        
        return true;
    }
    
    public static function isEnabled() {
        return self::$enabled;
    }
    
    public static function playSound($soundFile) {
        if (!self::$enabled || !self::$audioPlayer) {
            return false;
        }
        
        $soundPath = __DIR__ . '/../sounds/' . $soundFile;
        
        if (!file_exists($soundPath)) {
            return false;
        }
        
        // Executa em background para não bloquear o jogo
        $command = '';
        
        switch (self::$audioPlayer) {
            case 'ffplay':
                $command = "ffplay -nodisp -autoexit -loglevel quiet '{$soundPath}' > /dev/null 2>&1 &";
                break;
            case 'aplay':
                $command = "aplay -q '{$soundPath}' > /dev/null 2>&1 &";
                break;
            case 'paplay':
                $command = "paplay '{$soundPath}' > /dev/null 2>&1 &";
                break;
            case 'mpg123':
                $command = "mpg123 -q '{$soundPath}' > /dev/null 2>&1 &";
                break;
        }
        
        if ($command) {
            exec($command);
            return true;
        }
        
        return false;
    }
    
    public static function playAttackSound() {
        self::playSound('attack.wav');
    }
    
    public static function playHitSound() {
        self::playSound('hit.wav');
    }
    
    public static function playSpecialSound() {
        self::playSound('special.wav');
    }
    
    public static function playMugetsuSound() {
        self::playSound('mugetsu.mp3');
    }
    
    public static function playVictorySound() {
        self::playSound('victory.mp3');
    }
    
    public static function playMenuSound() {
        self::playSound('menu.wav');
    }
    
    public static function playErrorSound() {
        self::playSound('error.wav');
    }
    
    private static function commandExists($command) {
        $returnCode = 0;
        $output = [];
        exec("which {$command} 2>/dev/null", $output, $returnCode);
        return $returnCode === 0;
    }
    
    public static function createSoundDirectory() {
        $soundDir = __DIR__ . '/../sounds';
        if (!is_dir($soundDir)) {
            mkdir($soundDir, 0755, true);
        }
        return $soundDir;
    }
    
    public static function generateTestSounds() {
        $soundDir = self::createSoundDirectory();
        
        // Gera alguns sons de teste usando sox (se disponível)
        if (self::commandExists('sox')) {
            // Som de ataque
            exec("sox -n -n synth 0.1 sine 440 vol 0.5 '{$soundDir}/attack.wav' 2>/dev/null");
            
            // Som de hit
            exec("sox -n -n synth 0.2 sine 220 vol 0.7 '{$soundDir}/hit.wav' 2>/dev/null");
            
            // Som de especial
            exec("sox -n -n synth 0.3 sine 880 vol 0.6 '{$soundDir}/special.wav' 2>/dev/null");
            
            // Som de erro
            exec("sox -n -n synth 0.2 sine 110 vol 0.4 '{$soundDir}/error.wav' 2>/dev/null");
            
            // Som de menu
            exec("sox -n -n synth 0.05 sine 660 vol 0.3 '{$soundDir}/menu.wav' 2>/dev/null");
            
            return true;
        }
        
        return false;
    }
}
?>
