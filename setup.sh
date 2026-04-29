#!/bin/bash

echo "═══════════════════════════════════════════════════"
echo "  Farmacia Vida — Configuración Inicial"
echo "═══════════════════════════════════════════════════"
echo ""

# 1. Crear .env si no existe
if [ ! -f ./src/.env ]; then
    echo "📄 Creando archivo .env..."
    cp ./src/.env.example ./src/.env
fi

# 2. Levantar contenedores
echo "🐳 Levantando contenedores..."
docker-compose up -d --build

# 3. Esperar MySQL
echo "⏳ Esperando a MySQL..."
sleep 15

# 4. Instalar dependencias PHP
echo "📦 Instalando dependencias PHP..."
docker exec -it farmacia_php bash -c "
  cd /var/www/html &&
  composer install
"

# 5. Generar APP_KEY
echo "🔑 Generando APP_KEY..."
docker exec -it farmacia_php bash -c "
  cd /var/www/html &&
  php artisan key:generate
"

# 6. Permisos
echo "🔐 Configurando permisos..."
docker exec -it farmacia_php bash -c "
  chmod -R 775 storage bootstrap/cache &&
  chown -R www-data:www-data storage bootstrap/cache
"

# 7. Migraciones + seeders
echo "🗄️ Ejecutando migraciones y seeders..."
docker exec -it farmacia_php bash -c "
  cd /var/www/html &&
  php artisan migrate --seed --force
"

# 8. Node
echo "📦 Instalando dependencias Node..."
docker exec -it farmacia_node sh -c "
  cd /var/www/html &&
  npm install
"

# 9. Build assets
echo "🔨 Compilando assets..."
docker exec -it farmacia_node sh -c "
  cd /var/www/html &&
  npm run build
"

# 10. Limpiar caché
echo "🧹 Limpiando caché..."
docker exec -it farmacia_php bash -c "
  cd /var/www/html &&
  php artisan optimize:clear
"

echo ""
echo "═══════════════════════════════════════════════════"
echo "  ✅ Instalación completada"
echo ""
echo "  🌐 Accede al sistema en: http://localhost:8080"
echo "═══════════════════════════════════════════════════"