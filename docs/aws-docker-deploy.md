# Despliegue en AWS EC2 con Docker

Esta configuracion esta pensada para una instancia EC2 con Docker Compose. La aplicacion corre con Nginx, PHP-FPM/Laravel y MySQL en una red privada de Docker. Solo se publica HTTP; MySQL no se expone a internet.

El archivo con secretos reales se llama `.env.aws`. No debe subirse a Git. En el repositorio solo va `.env.aws.example`, y en la instancia se genera `.env.aws` con claves y passwords reales.

## 1. Crear la instancia EC2

Recomendacion inicial:

- AMI: Ubuntu Server LTS.
- Tipo: `t3.small` o superior. Para pruebas puede servir `t3.micro`, pero 2 GB de RAM es mas comodo para construir imagenes Docker.
- Almacenamiento: 20 GB o mas.
- Security Group:
  - `22/tcp` solo desde tu IP.
  - `80/tcp` desde internet.
  - `443/tcp` desde internet si despues agregas HTTPS.
  - No abras `3306/tcp`.

Conectate por SSH:

```bash
ssh -i /ruta/a/tu-llave.pem ubuntu@IP_PUBLICA_DE_TU_EC2
```

## 2. Instalar Docker

Ejecuta esto dentro de la instancia:

```bash
sudo apt-get update
sudo apt-get install -y ca-certificates curl gnupg git
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list >/dev/null
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo usermod -aG docker "$USER"
```

Cierra la sesion SSH y vuelve a entrar para que el grupo `docker` aplique:

```bash
exit
ssh -i /ruta/a/tu-llave.pem ubuntu@IP_PUBLICA_DE_TU_EC2
```

Verifica Docker:

```bash
docker --version
docker compose version
```

## 3. Clonar el proyecto

Cambia `URL_DE_TU_REPOSITORIO` por la URL real del repo:

```bash
git clone URL_DE_TU_REPOSITORIO farmacia-vida
cd farmacia-vida
```

Si el repositorio es privado, primero configura tu llave SSH o usa el metodo de acceso que tengas definido para GitHub/GitLab/Bitbucket.

## 4. Crear `.env.aws`

Copia la plantilla:

```bash
cp .env.aws.example .env.aws
```

Genera valores seguros:

```bash
APP_KEY_VALUE="$(docker run --rm php:8.3-cli php -r 'echo "base64:".base64_encode(random_bytes(32));')"
MYSQL_ROOT_PASSWORD_VALUE="$(openssl rand -base64 32 | tr -d '\n')"
MYSQL_PASSWORD_VALUE="$(openssl rand -base64 32 | tr -d '\n')"
PUBLIC_IP_VALUE="$(curl -s https://checkip.amazonaws.com | tr -d '\n')"
```

Escribe esos valores en `.env.aws`:

```bash
sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY_VALUE}|" .env.aws
sed -i "s|^APP_URL=.*|APP_URL=http://${PUBLIC_IP_VALUE}|" .env.aws
sed -i "s|^MYSQL_ROOT_PASSWORD=.*|MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD_VALUE}|" .env.aws
sed -i "s|^MYSQL_PASSWORD=.*|MYSQL_PASSWORD=${MYSQL_PASSWORD_VALUE}|" .env.aws
```

Confirma que ya no quedaron placeholders:

```bash
grep -E 'APP_KEY|APP_URL|MYSQL_ROOT_PASSWORD|MYSQL_DATABASE|MYSQL_USER|MYSQL_PASSWORD' .env.aws
```

Debe verse parecido a esto, pero con tus valores reales:

```env
APP_KEY=base64:...
APP_URL=http://IP_PUBLICA_DE_TU_EC2
MYSQL_ROOT_PASSWORD=...
MYSQL_DATABASE=farmacia_vida
MYSQL_USER=farmacia_user
MYSQL_PASSWORD=...
```

Importante: no cambies `DB_HOST`; dentro de Docker el host correcto es `mysql` y ya esta definido en `docker-compose.prod.yml`.

## 5. Levantar produccion

Construye y levanta los servicios:

```bash
docker compose --env-file .env.aws -f docker-compose.prod.yml up -d --build
```

Verifica estado:

```bash
docker compose --env-file .env.aws -f docker-compose.prod.yml ps
```

Los servicios `mysql`, `php` y `nginx` deben aparecer como `healthy`.

Revisa logs si algo tarda:

```bash
docker compose --env-file .env.aws -f docker-compose.prod.yml logs -f mysql php nginx
```

Prueba desde la instancia:

```bash
curl -I http://localhost/up
```

Luego abre en tu navegador:

```text
http://IP_PUBLICA_DE_TU_EC2
```

## 6. Comandos utiles

Ver contenedores:

```bash
docker compose --env-file .env.aws -f docker-compose.prod.yml ps
```

Reiniciar:

```bash
docker compose --env-file .env.aws -f docker-compose.prod.yml restart
```

Actualizar despues de hacer `git pull`:

```bash
git pull
docker compose --env-file .env.aws -f docker-compose.prod.yml up -d --build
docker compose --env-file .env.aws -f docker-compose.prod.yml exec php php artisan migrate --force
```

Entrar a Artisan:

```bash
docker compose --env-file .env.aws -f docker-compose.prod.yml exec php php artisan about
```

Entrar a MySQL:

```bash
docker compose --env-file .env.aws -f docker-compose.prod.yml exec mysql mysql -u root -p
```

Respaldar la base:

```bash
docker compose --env-file .env.aws -f docker-compose.prod.yml exec mysql mysqldump -u root -p farmacia_vida > farmacia_vida_backup.sql
```

Apagar sin borrar datos:

```bash
docker compose --env-file .env.aws -f docker-compose.prod.yml down
```

No uses `down -v` en produccion salvo que quieras borrar la base de datos, porque elimina el volumen persistente de MySQL.

## 7. HTTPS

Para produccion real usa HTTPS. Lo mas limpio en AWS es poner un Application Load Balancer o CloudFront con certificado de AWS Certificate Manager delante de la instancia. Si vas a exponer directamente la EC2, puedes agregar Caddy o Nginx/Certbot como proxy TLS y redirigir `443` hacia este compose.
