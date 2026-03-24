#!/bin/bash
set -e

echo "=== CrohnCare Deploy ==="

# 1. Build do frontend
echo "[1/6] Building frontend..."
cd frontend
npm ci --prefer-offline
VITE_API_URL=/api npm run build
cd ..

# 2. Instalar dependências do backend
echo "[2/6] Installing backend dependencies..."
cd backend
composer install --no-dev --optimize-autoloader --no-interaction
cd ..

# 3. Configurar .env do Laravel (se não existir)
if [ ! -f backend/.env ]; then
    echo "[3/6] Criando .env do Laravel..."
    cp backend/.env.example backend/.env
    # Ajustar para produção
    sed -i 's/APP_ENV=local/APP_ENV=production/' backend/.env
    sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' backend/.env
    sed -i "s|APP_URL=.*|APP_URL=http://$(curl -s ifconfig.me)|" backend/.env
else
    echo "[3/6] .env já existe, pulando..."
fi

# 4. Subir containers
echo "[4/6] Starting containers..."
docker compose -f docker-compose.prod.yml up -d --build

# 5. Laravel setup
echo "[5/6] Running Laravel setup..."
docker compose -f docker-compose.prod.yml exec app php artisan key:generate --force 2>/dev/null || true
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan storage:link --force
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache

# 6. Permissões
echo "[6/6] Setting permissions..."
docker compose -f docker-compose.prod.yml exec app chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache

echo ""
echo "=== Deploy concluído! ==="
echo "Acesse: http://$(curl -s ifconfig.me)"
