#!/bin/bash

echo "🚀 Iniciando Battle Arena Server..."

# Verifica se o PHP está instalado
if ! command -v php &> /dev/null; then
    echo "❌ PHP não encontrado. Por favor, instale o PHP 8.1+"
    exit 1
fi

# Verifica se a porta 8000 está em uso
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "⚠️  Porta 8000 já está em uso. Tentando liberar..."
    pkill -f "php -S localhost:8000" 2>/dev/null
    sleep 2
fi

# Inicia o servidor PHP
echo "📍 Iniciando servidor em http://localhost:8000"
echo "🛑 Pressione Ctrl+C para parar o servidor"
echo ""

# Abre o navegador após 2 segundos
(sleep 2 && xdg-open http://localhost:8000 2>/dev/null || open http://localhost:8000 2>/dev/null || echo "🌐 Abra http://localhost:8000 no seu navegador") &

# Inicia o servidor PHP
php -S localhost:8000 -t . 2>&1
