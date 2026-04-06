<?php


spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/src/Controllers/',
        __DIR__ . '/src/Views/',
        __DIR__ . '/src/Models/',
        __DIR__ . '/src/Services/',
        __DIR__ . '/config/',
        __DIR__ . '/classes/',  
        __DIR__ . '/managers/' 
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Inicializa o jogo
try {
    $gameController = new GameController();
    $gameController->start();
} catch (Exception $e) {
    echo "\033[91mERRO: " . $e->getMessage() . "\033[0m\n";
    exit(1);
}
