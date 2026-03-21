#!/bin/bash
set -e

echo "=== CrohnCare — Setup do Servidor ==="

# 1. Atualizar sistema
echo "[1/6] Atualizando sistema..."
apt-get update && apt-get upgrade -y

# 2. Instalar Docker
echo "[2/6] Instalando Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com | sh
    systemctl enable docker
    systemctl start docker
else
    echo "Docker já instalado."
fi

# 3. Instalar Node.js 20 (para build do frontend)
echo "[3/6] Instalando Node.js..."
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
else
    echo "Node.js já instalado."
fi

# 4. Instalar Composer
echo "[4/6] Instalando Composer..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
else
    echo "Composer já instalado."
fi

# 5. Instalar PHP CLI + extensões (para composer install fora do container)
echo "[5/6] Instalando PHP CLI..."
if ! command -v php &> /dev/null; then
    apt-get install -y php-cli php-mbstring php-xml php-curl php-zip unzip
else
    echo "PHP CLI já instalado."
fi

# 6. Configurar firewall
echo "[6/6] Configurando firewall..."
if command -v ufw &> /dev/null; then
    ufw allow 22/tcp
    ufw allow 80/tcp
    ufw allow 443/tcp
    ufw --force enable
fi

echo ""
echo "=== Setup concluído! ==="
echo "Agora rode: cd /opt/chroncare && bash deploy.sh"
