#!/bin/bash

# BATTLE ARENA - Terminal Game Launcher

cd "$(dirname "$0")"

echo "🎮 Battle Arena - Terminal Edition"
echo "=================================="
echo ""

# Verifica se PHP está instalado
if ! command -v php &> /dev/null; then
    echo "❌ PHP não encontrado. Por favor, instale o PHP 8.1+"
    exit 1
fi

# Verifica se o arquivo do jogo existe
if [ ! -f "battle-cli.php" ]; then
    echo "❌ Arquivo battle-cli.php não encontrado!"
    exit 1
fi

# Inicia o jogo
echo "🚀 Iniciando Battle Arena Terminal..."
echo ""

php battle-cli.php

echo ""
echo "👋 Obrigado por jogar!"
