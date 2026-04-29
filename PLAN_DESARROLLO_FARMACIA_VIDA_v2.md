# 🏥 PLAN DE DESARROLLO COMPLETO — SISTEMA FARMACIA Y CONSULTORIO MÉDICO "VIDA"
## Prompt Maestro para Agente de IA — Versión 2.0 (Actualizado)

> **Stack tecnológico:** Laravel 11 (MVC) · Bootstrap 5 · MySQL 8 · Docker Desktop
> **Nombre de la base de datos:** `farmacia_vida`
> **Roles del sistema:** `dueno` · `vendedor` · `medico`
> **Tipografía:** Outfit (títulos) + DM Sans (cuerpo) + DM Mono (códigos/folios)
> **Paleta:** `#0F172A` primario · `#F1F5F9` fondo · `#0D9488` acento · `#F59E0B` alerta · `#F43F5E` error · `#22C55E` éxito

---

## CONTEXTO DEL SISTEMA

La **Farmacia y Consultorio Médico "Vida"** opera actualmente de forma completamente manual. El sistema automatizará **8 módulos**:

| Módulo | Clave | Descripción |
|--------|-------|-------------|
| Autenticación y Control de Acceso | MO_01 | Login, roles, sesiones, gestión de usuarios |
| Panel de Inicio | MO_02 | Resumen operativo personalizado por rol |
| Ventas / Punto de Venta | MO_03 | Registro de ventas con método FEFO, comprobantes |
| Control de Inventario | MO_04 | Productos, lotes, categorías, kardex, alertas |
| Gestión de Proveedores y Pedidos | MO_05 | Proveedores con RFC, pedidos, recepción con lotes |
| Gestión de Citas Médicas | MO_06 | Agenda, citas, detección de traslapes |
| Consulta Médica y Expediente Clínico | MO_07 | Expedientes, consultas, recetas médicas |
| Reportes | MO_08 | Reportes descargables en PDF (ventas, inventario, consultas) |

**Actores:** Dueño (ACT-01) · Vendedor (ACT-02) · Médico (ACT-04)

**Casos de Uso:**
- CU-01: Gestionar Sesión
- CU-02: Visualizar Panel de Inicio
- CU-03: Administrar Usuarios del Sistema
- CU-04: Registrar Venta (FEFO)
- CU-05: Gestionar Producto del Inventario
- CU-06: Consultar Catálogo de Inventario
- CU-07: Gestionar Proveedor
- CU-08: Gestionar Ciclo de Pedido a Proveedor
- CU-09: Gestionar Cita Médica
- CU-10: Consultar Agenda Médica
- CU-11: Crear Expediente Clínico
- CU-12: Registrar Consulta Médica
- CU-13: Generar Receta Médica Digital
- CU-14: Generar Reportes

---

## INSTRUCCIONES GENERALES PARA EL AGENTE

1. Sigue el orden de los sprints. Cada sprint es una unidad funcional completa.
2. Usa el patrón **MVC de Laravel 11** en todo momento.
3. Toda vista usa el layout `resources/views/layouts/app.blade.php` con sidebar de navegación por rol.
4. El sistema debe estar completamente en **español**.
5. Usa **sesiones de Laravel** para autenticación, con middleware de rol y timeout de inactividad.
6. Genera **folios únicos** con formato: `VTA-YYYYMMDD-NNNN`, `PED-YYYYMMDD-NNNN`, `REC-YYYYMMDD-NNNN`.
7. **Soft-delete lógico** (campo `estado activo/inactivo`) en: usuarios, productos, proveedores, expedientes.
8. La búsqueda de productos en ventas usa **AJAX + JSON** sin recargar la página.
9. Importa **Lucide Icons** vía CDN. Importa **Outfit** y **DM Sans** desde Google Fonts.
10. Registra **cada movimiento de stock** en `kardex_producto` automáticamente.
11. El stock se gestiona a nivel de **lote**. Al vender, descuenta usando **método FEFO** (fecha de vencimiento más próxima primero). El campo `stock_total` en `producto` es la suma de cantidades de todos sus lotes activos.
12. Los reportes se deben poder **descargar en PDF** usando la librería `barryvdh/laravel-dompdf`.
13. Al iniciar sesión, el panel muestra alertas de **stock crítico** (stock_total ≤ stock_minimo) y **lotes próximos a vencer** (≤30 días).
14. Timeout de sesión: **20 minutos de inactividad**. Redirige al login.
15. **No hay bloqueo por intentos fallidos** en esta versión del sistema.
16. El **Vendedor** puede también gestionar citas (CU-09 y CU-10).

---

## CONFIGURACIÓN DOCKER — SPRINT 0 (Entorno de Desarrollo)

### Paso 0.1 — Estructura de directorios del proyecto

```
farmacia-vida/
├── docker-compose.yml
├── nginx/
│   └── default.conf
├── mysql/
│   └── init.sql          ← Script SQL completo de la BD
└── src/                  ← Proyecto Laravel
```

### Paso 0.2 — Archivo `docker-compose.yml`

```yaml
version: '3.8'

services:

  nginx:
    image: nginx:alpine
    container_name: farmacia_nginx
    ports:
      - "8080:80"
    volumes:
      - ./src:/var/www/html
      - ./nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php
    networks:
      - farmacia_net

  php:
    image: php:8.3-fpm
    container_name: farmacia_php
    working_dir: /var/www/html
    volumes:
      - ./src:/var/www/html
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
    networks:
      - farmacia_net
    command: >
      bash -c "
        apt-get update -y &&
        apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip unzip git curl libonig-dev libxml2-dev libzip-dev &&
        docker-php-ext-configure gd --with-freetype --with-jpeg &&
        docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip &&
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer &&
        php-fpm
      "

  mysql:
    image: mysql:8.0
    container_name: farmacia_mysql
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: root_secret
      MYSQL_DATABASE: farmacia_vida
      MYSQL_USER: farmacia_user
      MYSQL_PASSWORD: farmacia_pass
    ports:
      - "3306:3306"
    volumes:
      - farmacia_mysql_data:/var/lib/mysql
      - ./mysql/init.sql:/docker-entrypoint-initdb.d/init.sql
    networks:
      - farmacia_net

  node:
    image: node:20-alpine
    container_name: farmacia_node
    working_dir: /var/www/html
    volumes:
      - ./src:/var/www/html
    networks:
      - farmacia_net
    tty: true

volumes:
  farmacia_mysql_data:

networks:
  farmacia_net:
    driver: bridge
```

### Paso 0.3 — Archivo `nginx/default.conf`

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Paso 0.4 — Archivo `mysql/init.sql`

Copia el **script SQL completo** de la base de datos (versión 2 con tablas `lote`, campos `rfc`/`correo_electronico` en proveedor, `stock_total` en producto, etc.).

### Paso 0.5 — Comandos de instalación y arranque

```bash
# 1. Levantar contenedores
docker-compose up -d

# 2. Esperar ~30 segundos a que MySQL inicialice
docker-compose ps

# 3. Instalar Laravel 11
docker exec -it farmacia_php bash -c "
  cd /var/www/html &&
  composer create-project laravel/laravel . --prefer-dist
"

# 4. Instalar dompdf para exportar PDF
docker exec -it farmacia_php bash -c "
  cd /var/www/html &&
  composer require barryvdh/laravel-dompdf
"

# 5. Configurar .env
# Abre src/.env y establece:
#   DB_CONNECTION=mysql
#   DB_HOST=mysql
#   DB_PORT=3306
#   DB_DATABASE=farmacia_vida
#   DB_USERNAME=farmacia_user
#   DB_PASSWORD=farmacia_pass
#   APP_NAME="Farmacia Vida"
#   APP_URL=http://localhost:8080
#   SESSION_LIFETIME=20
#   APP_LOCALE=es

docker exec -it farmacia_php bash -c "
  cd /var/www/html &&
  cp .env.example .env &&
  php artisan key:generate
"

# 6. Instalar dependencias Node.js y Bootstrap
docker exec -it farmacia_node sh -c "
  cd /var/www/html &&
  npm install &&
  npm install --save-dev bootstrap @popperjs/core
"

# 7. Verificar conexión a BD
docker exec -it farmacia_php php artisan db:show

# 8. Permisos
docker exec -it farmacia_php bash -c "
  chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache &&
  chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
"
```

### Paso 0.6 — Comandos útiles de mantenimiento

```bash
# Entrar a PHP (para artisan y composer)
docker exec -it farmacia_php bash

# Entrar a Node (para npm)
docker exec -it farmacia_node sh

# Ver logs de Laravel
docker exec -it farmacia_php bash -c "tail -f /var/www/html/storage/logs/laravel.log"

# Conectar a MySQL
docker exec -it farmacia_mysql mysql -u farmacia_user -pfarmacia_pass farmacia_vida

# Compilar assets (producción)
docker exec -it farmacia_node sh -c "cd /var/www/html && npm run build"

# Modo desarrollo
docker exec -it farmacia_node sh -c "cd /var/www/html && npm run dev"

# Reiniciar todo
docker-compose restart

# Apagar (conserva BD)
docker-compose down

# Apagar y borrar BD
docker-compose down -v
```

---

## SPRINT 1 — Autenticación, Layout Base, Panel de Inicio (MO_01 + MO_02)

**Objetivo:** Sistema de login funcional (CU-01), layout principal con sidebar por rol, panel de inicio con indicadores y alertas (CU-02), gestión de usuarios (CU-03).

> ⚠️ **Este sprint reemplaza la implementación anterior.** Si ya ejecutaste Sprint 1, borra el código de los controladores, modelos y vistas afectados y reemplázalos íntegramente con lo que sigue.

### 1.1 — Modelos Eloquent

**IMPORTANTE:** La base de datos ya está creada por `init.sql`. NO uses migraciones de Laravel.

```bash
docker exec -it farmacia_php bash
cd /var/www/html

# Crear todos los modelos (sin migración)
php artisan make:model Usuario
php artisan make:model Categoria
php artisan make:model Producto
php artisan make:model Lote
php artisan make:model Proveedor
php artisan make:model DiaVisitaProveedor
php artisan make:model KardexProducto
php artisan make:model Pedido
php artisan make:model DetallePedido
php artisan make:model Venta
php artisan make:model DetalleVenta
php artisan make:model Receta
php artisan make:model DetalleReceta
php artisan make:model ExpedienteClinico
php artisan make:model Cita
php artisan make:model Consulta
```

**Archivo: `app/Models/Usuario.php`**

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table      = 'usuario';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = [
        'nombre_completo', 'username', 'password_hash', 'rol', 'estado',
    ];

    protected $hidden = ['password_hash'];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeMedicos($query)
    {
        return $query->where('rol', 'medico');
    }

    // Relaciones
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'vendedor_id');
    }

    public function consultas()
    {
        return $this->hasMany(Consulta::class, 'medico_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'medico_id');
    }

    public function kardexMovimientos()
    {
        return $this->hasMany(KardexProducto::class, 'usuario_id');
    }
}
```

**Archivo: `app/Models/Producto.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table      = 'producto';
    public    $timestamps = false;

    protected $fillable = [
        'proveedor_id', 'categoria_id', 'nombre',
        'precio_compra', 'precio_venta',
        'stock_total',  // ← campo correcto (no stock_actual)
        'stock_minimo', 'requiere_receta', 'estado',
    ];

    protected $casts = [
        'requiere_receta' => 'boolean',
        'precio_compra'   => 'decimal:2',
        'precio_venta'    => 'decimal:2',
    ];

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeStockCritico($query)
    {
        return $query->whereColumn('stock_total', '<=', 'stock_minimo')
                     ->where('estado', 'activo');
    }

    // No hay fecha_vencimiento en producto; está en lote
    // Productos con al menos un lote que vence en <= 30 días
    public function scopeConLotesProximosAVencer($query, int $dias = 30)
    {
        return $query->whereHas('lotes', function ($q) use ($dias) {
            $q->where('cantidad', '>', 0)
              ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias))
              ->whereDate('fecha_vencimiento', '>=', now());
        })->where('estado', 'activo');
    }

    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'producto_id')
                    ->orderBy('fecha_vencimiento'); // orden FEFO por defecto
    }

    public function lotesFEFO()
    {
        // Lotes con stock disponible ordenados por fecha de vencimiento (FEFO)
        return $this->hasMany(Lote::class, 'producto_id')
                    ->where('cantidad', '>', 0)
                    ->orderBy('fecha_vencimiento');
    }

    public function kardex()
    {
        return $this->hasMany(KardexProducto::class, 'producto_id');
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    /**
     * Recalcula y actualiza stock_total sumando cantidades de todos los lotes.
     * Llama a este método siempre que se modifiquen los lotes.
     */
    public function recalcularStock(): void
    {
        $this->update([
            'stock_total' => $this->lotes()->sum('cantidad'),
        ]);
    }
}
```

**Archivo: `app/Models/Lote.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $table      = 'lote';
    public    $timestamps = false;

    protected $fillable = [
        'producto_id', 'numero_lote', 'cantidad',
        'fecha_vencimiento', 'fecha_ingreso',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha_ingreso'     => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function scopeConStock($query)
    {
        return $query->where('cantidad', '>', 0);
    }

    public function scopeProximosAVencer($query, int $dias = 30)
    {
        return $query->conStock()
                     ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias))
                     ->whereDate('fecha_vencimiento', '>=', now());
    }
}
```

**Archivo: `app/Models/Proveedor.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table      = 'proveedor';
    public    $timestamps = false;

    protected $fillable = [
        'nombre_empresa', 'nombre_contacto', 'telefono',
        'rfc', 'correo_electronico', 'estado',   // ← nuevos campos
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function diasVisita()
    {
        return $this->hasMany(DiaVisitaProveedor::class, 'proveedor_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'proveedor_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }
}
```

**Archivo: `app/Models/Pedido.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table      = 'pedido';
    public    $timestamps = false;

    protected $fillable = [
        'proveedor_id', 'usuario_id', 'folio',
        'fecha_estimada', 'estado', 'monto_total', 'fecha_pago',
    ];

    protected $casts = [
        'fecha_estimada' => 'date',
        'fecha_pago'     => 'date',
        'monto_total'    => 'decimal:2',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    public static function generarFolio(): string
    {
        $fecha  = now()->format('Ymd');
        $ultimo = self::whereDate(\DB::raw('DATE(fecha_estimada)'), now()->toDateString())->count();
        return 'PED-' . $fecha . '-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
```

**Archivo: `app/Models/DetallePedido.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    protected $table      = 'detalle_pedido';
    public    $timestamps = false;

    protected $fillable = [
        'pedido_id', 'producto_id',
        'cantidad_solicitada', 'cantidad_recibida',
        'precio_compra_real',  // ← nuevo campo
    ];

    protected $casts = [
        'precio_compra_real' => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
```

**Archivo: `app/Models/ExpedienteClinico.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpedienteClinico extends Model
{
    protected $table      = 'expediente_clinico';
    public    $timestamps = false;

    protected $fillable = [
        'nombre_completo', 'fecha_nacimiento', 'sexo', 'tipo_sangre',
        'alergias', 'enfermedades_cronicas', 'medicamentos_actuales',
        'antecedentes_familiares',
        'telefono', 'correo', // ← nuevos campos
        'estado',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'expediente_id');
    }

    public function consultas()
    {
        return $this->hasMany(Consulta::class, 'expediente_id');
    }

    /**
     * Calcula la edad en años completos a partir de la fecha de nacimiento.
     */
    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
    }
}
```

Crea los modelos restantes (`Categoria`, `DiaVisitaProveedor`, `KardexProducto`, `Venta`, `DetalleVenta`, `Receta`, `DetalleReceta`, `Cita`, `Consulta`) siguiendo el mismo patrón: `$table`, `$timestamps = false`, `$fillable`, `$casts` y relaciones Eloquent correspondientes.

### 1.2 — Configurar Autenticación

**Archivo: `config/auth.php`** — Modifica el provider `web`:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\Models\Usuario::class,
    ],
],
```

### 1.3 — Middleware

```bash
php artisan make:middleware VerificarRol
php artisan make:middleware SesionActiva
```

**Archivo: `app/Http/Middleware/VerificarRol.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!in_array(auth()->user()->rol, $roles)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
```

**Archivo: `app/Http/Middleware/SesionActiva.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SesionActiva
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (auth()->check()) {
            $ultimaActividad = session('ultima_actividad');
            $timeout         = config('session.lifetime', 20) * 60;

            if ($ultimaActividad && (time() - $ultimaActividad) > $timeout) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')
                    ->withErrors(['timeout' => 'Su sesión expiró por inactividad. Inicie sesión nuevamente.']);
            }
            session(['ultima_actividad' => time()]);
        }

        return $next($request);
    }
}
```

Registra en `bootstrap/app.php` (Laravel 11):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'rol'           => \App\Http\Middleware\VerificarRol::class,
        'sesion.activa' => \App\Http\Middleware\SesionActiva::class,
    ]);
    $middleware->web(append: [
        \App\Http\Middleware\SesionActiva::class,
    ]);
})
```

### 1.4 — Controladores

```bash
php artisan make:controller Auth/LoginController
php artisan make:controller Auth/UsuarioController
php artisan make:controller DashboardController
```

**Archivo: `app/Http/Controllers/Auth/LoginController.php`**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Lote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // Buscar usuario activo (sin bloqueo por intentos en esta versión)
        $usuario = \App\Models\Usuario::where('username', $request->username)
                                      ->where('estado', 'activo')
                                      ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            return back()->withErrors([
                'username' => 'Nombre de usuario o contraseña incorrectos. Intente de nuevo.'
            ])->withInput(['username' => $request->username]);
        }

        Auth::login($usuario);
        $request->session()->regenerate();
        session(['ultima_actividad' => time()]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }
}
```

**Archivo: `app/Http/Controllers/DashboardController.php`** (implementa CU-02)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\Venta;
use App\Models\Consulta;
use App\Models\Cita;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'sesion.activa']);
    }

    public function index()
    {
        $rol = auth()->user()->rol;

        // ── Alertas de inventario (RF-13, RF-14) ──────────────────
        // Los primeros 5 productos en stock crítico
        $stockCritico = Producto::stockCritico()
                                ->with('categoria')
                                ->orderBy('stock_total')
                                ->limit(5)
                                ->get();

        // Los 5 lotes más próximos a vencer (≤30 días)
        $lotesProximosAVencer = Lote::proximosAVencer(30)
                                    ->with('producto.categoria')
                                    ->orderBy('fecha_vencimiento')
                                    ->limit(5)
                                    ->get();

        $totalProductos = Producto::activos()->count();

        $totalAlertas = Producto::stockCritico()->count()
                      + Lote::proximosAVencer(30)->count();

        // ── Indicadores por rol (RF-12) ────────────────────────────
        $ventasHoy        = null;
        $transaccionesHoy = null;
        $citasHoy         = null;
        $consultasHoy     = null;

        if (in_array($rol, ['dueno', 'vendedor'])) {
            $ventasHoy        = Venta::whereDate('fecha_hora', today())
                                     ->where('estado', 'completada')
                                     ->sum('total');
            $transaccionesHoy = Venta::whereDate('fecha_hora', today())
                                     ->where('estado', 'completada')
                                     ->count();
        }

        if ($rol === 'medico') {
            $citasHoy     = Cita::where('medico_id', auth()->id())
                                ->whereDate('fecha', today())
                                ->where('estado', 'programada')
                                ->count();
            $consultasHoy = Consulta::where('medico_id', auth()->id())
                                    ->whereDate('fecha_hora', today())
                                    ->count();
        }

        return view('dashboard', compact(
            'stockCritico',
            'lotesProximosAVencer',
            'totalProductos',
            'totalAlertas',
            'ventasHoy',
            'transaccionesHoy',
            'citasHoy',
            'consultasHoy'
        ));
    }
}
```

**Archivo: `app/Http/Controllers/Auth/UsuarioController.php`** (implementa CU-03)

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::orderBy('nombre_completo')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'username'        => 'required|string|max:60|unique:usuario,username',
            'password'        => 'required|string|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
            'rol'             => 'required|in:dueno,vendedor,medico',
        ], [
            'username.unique' => 'El nombre de usuario ya existe en el sistema.',
            'password.min'    => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex'  => 'La contraseña debe combinar letras y números.',
        ]);

        Usuario::create([
            'nombre_completo' => $request->nombre_completo,
            'username'        => $request->username,
            'password_hash'   => Hash::make($request->password),
            'rol'             => $request->rol,
            'estado'          => 'activo',
        ]);

        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(int $id)
    {
        $usuario = Usuario::findOrFail($id);
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, int $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'username'        => "required|string|max:60|unique:usuario,username,{$id}",
            'rol'             => 'required|in:dueno,vendedor,medico',
        ]);

        $usuario->update([
            'nombre_completo' => $request->nombre_completo,
            'username'        => $request->username,
            'rol'             => $request->rol,
        ]);

        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleEstado(int $id)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $nuevoEstado = $usuario->estado === 'activo' ? 'inactivo' : 'activo';
        $usuario->update(['estado' => $nuevoEstado]);

        $msg = $nuevoEstado === 'activo' ? 'activado' : 'desactivado';
        return back()->with('success', "Usuario {$msg} correctamente.");
    }

    public function resetPassword(int $id)
    {
        $usuario    = Usuario::findOrFail($id);
        $nuevaPass  = Str::random(10);

        $usuario->update(['password_hash' => Hash::make($nuevaPass)]);

        return back()->with('success', "Contraseña temporal: {$nuevaPass} — Comunícala al usuario.");
    }

    // Cambiar propia contraseña (FA_004 de CU-03)
    public function showCambiarPassword()
    {
        return view('usuarios.cambiar-password');
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required',
            'password_nuevo'  => 'required|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/|confirmed',
        ]);

        if (!Hash::check($request->password_actual, auth()->user()->password_hash)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual es incorrecta.']);
        }

        auth()->user()->update(['password_hash' => Hash::make($request->password_nuevo)]);
        return back()->with('success', 'Contraseña actualizada exitosamente.');
    }
}
```

### 1.5 — Rutas

**Archivo: `routes/web.php`**

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\UsuarioController;
use App\Http\Controllers\DashboardController;

// ── Autenticación ──────────────────────────────────────────
Route::get('/',       [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// ── Área autenticada ───────────────────────────────────────
Route::middleware(['auth', 'sesion.activa'])->group(function () {

    // Dashboard / Panel de Inicio (CU-02)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Cambiar contraseña propia (FA_004 de CU-03)
    Route::get('/perfil/password',   [UsuarioController::class, 'showCambiarPassword'])->name('perfil.password');
    Route::patch('/perfil/password', [UsuarioController::class, 'cambiarPassword'])->name('perfil.cambiar-password');

    // ── Gestión de Usuarios — solo Dueño (CU-03) ──────────
    Route::middleware('rol:dueno')->prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/',               [UsuarioController::class, 'index'])->name('index');
        Route::get('/crear',          [UsuarioController::class, 'create'])->name('create');
        Route::post('/',              [UsuarioController::class, 'store'])->name('store');
        Route::get('/{id}/editar',    [UsuarioController::class, 'edit'])->name('edit');
        Route::put('/{id}',           [UsuarioController::class, 'update'])->name('update');
        Route::patch('/{id}/estado',  [UsuarioController::class, 'toggleEstado'])->name('toggle-estado');
        Route::patch('/{id}/reset',   [UsuarioController::class, 'resetPassword'])->name('reset-password');
    });

    // ── Módulos de Sprints posteriores se agregan aquí ─────
});
```

### 1.6 — Vistas

#### Layout principal: `resources/views/layouts/app.blade.php`

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — Farmacia Vida</title>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=DM+Mono:wght@500&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <style>
        :root {
            --color-primary:    #0F172A;
            --color-secondary:  #F1F5F9;
            --color-accent:     #0D9488;
            --color-info:       #0EA5E9;
            --color-warning:    #F59E0B;
            --color-danger:     #F43F5E;
            --color-success:    #22C55E;
            --color-surface:    #FFFFFF;
            --color-text-main:  #0F172A;
            --color-text-muted: #64748B;
            --color-border:     #E2E8F0;
            --sidebar-width:    240px;
        }
        body { font-family: 'DM Sans', sans-serif; font-size: 14px; background: var(--color-secondary); color: var(--color-text-main); }
        h1,h2,h3,h4,h5,h6,.fw-bold { font-family: 'Outfit', sans-serif; }
        code,.folio { font-family: 'DM Mono', monospace; font-size: 12px; }

        /* Sidebar */
        #sidebar { width: var(--sidebar-width); min-height: 100vh; background: var(--color-primary); position: fixed; top: 0; left: 0; z-index: 1000; overflow-y: auto; }
        #sidebar .brand { padding: 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        #sidebar .brand span { font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: #fff; }
        #sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 10px 16px; border-radius: 8px; margin: 2px 8px; display: flex; align-items: center; gap: 10px; transition: all 0.15s; font-size: 14px; text-decoration: none; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { background: var(--color-accent); color: #fff; }
        #sidebar .nav-section { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.4); padding: 16px 16px 4px; }

        /* Topbar */
        #topbar { margin-left: var(--sidebar-width); background: var(--color-surface); border-bottom: 1px solid var(--color-border); padding: 12px 24px; position: sticky; top: 0; z-index: 999; display: flex; align-items: center; justify-content: space-between; }

        /* Main */
        #main-content { margin-left: var(--sidebar-width); padding: 24px; }

        /* Cards */
        .card { border: 1px solid var(--color-border); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .card-header { background: var(--color-surface); border-bottom: 1px solid var(--color-border); font-family: 'Outfit', sans-serif; font-weight: 600; }

        /* Botones */
        .btn-accent { background: var(--color-accent); color: #fff; border: none; }
        .btn-accent:hover { background: #0b7a72; color: #fff; }

        /* Badges */
        .badge-activo    { background: var(--color-success); color: #fff; }
        .badge-inactivo  { background: var(--color-text-muted); color: #fff; }
        .badge-pendiente { background: var(--color-warning); color: #0F172A; }
        .badge-recibido  { background: var(--color-info); color: #fff; }
        .badge-pagado    { background: var(--color-success); color: #fff; }
        .badge-cancelado { background: var(--color-danger); color: #fff; }
        .badge-critico   { background: var(--color-danger); color: #fff; }
    </style>

    @stack('styles')
</head>
<body>

{{-- SIDEBAR --}}
<nav id="sidebar">
    <div class="brand d-flex align-items-center gap-2">
        <i data-lucide="cross" style="color:var(--color-accent);width:24px;height:24px;"></i>
        <span>Farmacia Vida</span>
    </div>

    <div class="mt-2">
        <div class="nav-section">Principal</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard" style="width:20px;height:20px;"></i> Panel de Inicio
        </a>

        @if(in_array(auth()->user()->rol, ['dueno', 'vendedor']))
        <div class="nav-section">Farmacia</div>
        <a href="{{ route('ventas.index') }}" class="nav-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}">
            <i data-lucide="shopping-cart" style="width:20px;height:20px;"></i> Ventas
        </a>
        <a href="{{ route('productos.index') }}" class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}">
            <i data-lucide="package" style="width:20px;height:20px;"></i> Inventario
        </a>
        @endif

        @if(auth()->user()->rol === 'dueno')
        <a href="{{ route('proveedores.index') }}" class="nav-link {{ request()->routeIs('proveedores.*') ? 'active' : '' }}">
            <i data-lucide="truck" style="width:20px;height:20px;"></i> Proveedores
        </a>
        <a href="{{ route('pedidos.index') }}" class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}">
            <i data-lucide="clipboard-list" style="width:20px;height:20px;"></i> Pedidos
        </a>
        @endif

        @if(in_array(auth()->user()->rol, ['dueno', 'medico', 'vendedor']))
        <div class="nav-section">Consultorio</div>
        <a href="{{ route('citas.index') }}" class="nav-link {{ request()->routeIs('citas.*') ? 'active' : '' }}">
            <i data-lucide="calendar-days" style="width:20px;height:20px;"></i> Agenda / Citas
        </a>
        @endif

        @if(in_array(auth()->user()->rol, ['dueno', 'medico']))
        <a href="{{ route('expedientes.index') }}" class="nav-link {{ request()->routeIs('expedientes.*') ? 'active' : '' }}">
            <i data-lucide="folder-open" style="width:20px;height:20px;"></i> Expedientes
        </a>
        @endif

        @if(auth()->user()->rol === 'dueno')
        <div class="nav-section">Administración</div>
        <a href="{{ route('usuarios.index') }}" class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
            <i data-lucide="users" style="width:20px;height:20px;"></i> Usuarios
        </a>
        <a href="{{ route('reportes.index') }}" class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
            <i data-lucide="bar-chart-2" style="width:20px;height:20px;"></i> Reportes
        </a>
        @endif
    </div>
</nav>

{{-- TOPBAR --}}
<div id="topbar">
    <div>
        <h6 class="mb-0 fw-bold" style="font-size:15px;">@yield('page-title', 'Panel de Inicio')</h6>
    </div>
    <div class="d-flex align-items-center gap-3">
        @php $totalAlertas = (isset($totalAlertas) ? $totalAlertas : 0); @endphp
        @if($totalAlertas > 0)
        <span class="d-flex align-items-center gap-1" style="color:var(--color-danger);">
            <i data-lucide="alert-triangle" style="width:18px;height:18px;"></i>
            <span class="badge" style="background:var(--color-danger);">{{ $totalAlertas }}</span>
        </span>
        @endif

        <div class="d-flex align-items-center gap-2">
            <div style="width:32px;height:32px;border-radius:50%;background:var(--color-accent);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;">
                {{ strtoupper(substr(auth()->user()->nombre_completo, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:13px;font-weight:600;">{{ auth()->user()->nombre_completo }}</div>
                <div style="font-size:11px;color:var(--color-text-muted);">{{ ucfirst(auth()->user()->rol) }}</div>
            </div>
        </div>

        <a href="{{ route('perfil.password') }}" class="btn btn-sm" style="color:var(--color-text-muted);border:none;background:none;" title="Cambiar contraseña">
            <i data-lucide="settings" style="width:18px;height:18px;"></i>
        </a>

        <form action="{{ route('logout') }}" method="POST" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-sm" style="color:var(--color-text-muted);border:none;background:none;" title="Cerrar sesión">
                <i data-lucide="log-out" style="width:18px;height:18px;"></i>
            </button>
        </form>
    </div>
</div>

{{-- CONTENIDO PRINCIPAL --}}
<main id="main-content">
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
            <i data-lucide="check-circle" style="width:18px;height:18px;"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error') || $errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
            <i data-lucide="alert-circle" style="width:18px;height:18px;"></i>
            {{ session('error') ?? $errors->first() }}
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>lucide.createIcons();</script>
@stack('scripts')
</body>
</html>
```

#### Vista Login: `resources/views/auth/login.blade.php`

Página limpia sin layout (fondo oscuro `#0F172A` con patrón de puntos), card central con logo de cruz en color acento, campos Usuario y Contraseña, botón "Iniciar Sesión" en color primario oscuro, leyenda "Sesión segura". Muestra mensajes de error de validación. Muestra mensaje de timeout si existe `session error`.

#### Vista Dashboard: `resources/views/dashboard.blade.php`

```html
@extends('layouts.app')
@section('title', 'Panel de Inicio')
@section('page-title', 'Panel de Inicio')

@section('content')
<div class="mb-4">
    <p class="text-muted mb-0" style="font-size:13px;">
        {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
    </p>
</div>

{{-- Tarjetas de indicadores según rol (RF-12) --}}
@if(in_array(auth()->user()->rol, ['dueno', 'vendedor']))
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:12px;color:var(--color-text-muted);">Ventas Hoy</div>
                    <div style="font-size:24px;font-weight:700;font-family:'Outfit',sans-serif;">
                        ${{ number_format($ventasHoy ?? 0, 2) }}
                    </div>
                </div>
                <i data-lucide="dollar-sign" style="width:20px;height:20px;color:var(--color-accent);"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:12px;color:var(--color-text-muted);">Transacciones</div>
                    <div style="font-size:24px;font-weight:700;font-family:'Outfit',sans-serif;">
                        {{ $transaccionesHoy ?? 0 }}
                    </div>
                </div>
                <i data-lucide="receipt" style="width:20px;height:20px;color:var(--color-info);"></i>
            </div>
        </div>
    </div>
    @if(auth()->user()->rol === 'dueno')
    <div class="col-md-3">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:12px;color:var(--color-text-muted);">Total Productos</div>
                    <div style="font-size:24px;font-weight:700;font-family:'Outfit',sans-serif;">
                        {{ number_format($totalProductos) }}
                    </div>
                </div>
                <i data-lucide="package" style="width:20px;height:20px;color:var(--color-warning);"></i>
            </div>
        </div>
    </div>
    @endif
    <div class="col-md-3">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:12px;color:var(--color-text-muted);">Alertas Activas</div>
                    <div style="font-size:24px;font-weight:700;font-family:'Outfit',sans-serif;color:var(--color-danger);">
                        {{ $totalAlertas }}
                    </div>
                </div>
                <i data-lucide="alert-circle" style="width:20px;height:20px;color:var(--color-danger);"></i>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Indicadores para Médico --}}
@if(auth()->user()->rol === 'medico')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:12px;color:var(--color-text-muted);">Citas Hoy</div>
                    <div style="font-size:24px;font-weight:700;font-family:'Outfit',sans-serif;">{{ $citasHoy ?? 0 }}</div>
                </div>
                <i data-lucide="calendar-days" style="width:20px;height:20px;color:var(--color-accent);"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:12px;color:var(--color-text-muted);">Consultas Hoy</div>
                    <div style="font-size:24px;font-weight:700;font-family:'Outfit',sans-serif;">{{ $consultasHoy ?? 0 }}</div>
                </div>
                <i data-lucide="stethoscope" style="width:20px;height:20px;color:var(--color-info);"></i>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-3">
    {{-- Stock Crítico (RF-13) --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3 px-4">
                <span class="d-flex align-items-center gap-2">
                    <i data-lucide="alert-triangle" style="width:18px;height:18px;color:var(--color-danger);"></i>
                    Stock Crítico
                </span>
                <a href="{{ route('productos.index', ['filtro'=>'critico']) }}" style="font-size:12px;color:var(--color-accent);">Ver todos</a>
            </div>
            <div class="card-body p-0">
                @forelse($stockCritico as $producto)
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                    <div>
                        <div style="font-size:13px;font-weight:600;">{{ $producto->nombre }}</div>
                        <div style="font-size:12px;color:var(--color-text-muted);">{{ $producto->categoria->nombre }}</div>
                    </div>
                    <span class="badge badge-critico">{{ $producto->stock_total }} unidades</span>
                </div>
                @empty
                <div class="px-4 py-3 text-muted" style="font-size:13px;">
                    <i data-lucide="check-circle" style="width:14px;height:14px;color:var(--color-success);"></i>
                    Sin alertas de stock crítico
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Próximos a Vencer (RF-14) --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3 px-4">
                <span class="d-flex align-items-center gap-2">
                    <i data-lucide="calendar-x" style="width:18px;height:18px;color:var(--color-warning);"></i>
                    Próximos a Vencer
                </span>
                <a href="{{ route('productos.index', ['filtro'=>'vencer']) }}" style="font-size:12px;color:var(--color-accent);">Ver reporte</a>
            </div>
            <div class="card-body p-0">
                @forelse($lotesProximosAVencer as $lote)
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                    <div>
                        <div style="font-size:13px;font-weight:600;">{{ $lote->producto->nombre }}</div>
                        <div style="font-size:12px;color:var(--color-text-muted);">Lote: <code>{{ $lote->numero_lote }}</code></div>
                    </div>
                    <div class="text-end">
                        <span class="badge" style="background:var(--color-warning);color:#0F172A;font-size:11px;">
                            {{ $lote->fecha_vencimiento->diffInDays(now()) }} días
                        </span>
                        <div style="font-size:11px;color:var(--color-text-muted);">
                            VENCE: {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-4 py-3 text-muted" style="font-size:13px;">
                    <i data-lucide="check-circle" style="width:14px;height:14px;color:var(--color-success);"></i>
                    Sin productos próximos a vencer
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
```

### 1.7 — Vistas de Usuarios

**`resources/views/usuarios/index.blade.php`** — Tabla con columnas: Nombre · Usuario · Rol (badge) · Estado (badge) · Acciones (Editar, Activar/Desactivar, Restablecer contraseña). Barra de búsqueda por nombre o usuario. Botón "+ Nuevo Usuario".

**`resources/views/usuarios/create.blade.php`** y **`edit.blade.php`** — Formulario con: nombre completo, username, contraseña (solo en create), rol (select). Validación visual con `@error`.

**`resources/views/usuarios/cambiar-password.blade.php`** — Formulario: contraseña actual, nueva contraseña, confirmar contraseña.

### 1.8 — Comandos finales del Sprint 1

```bash
# Configurar paginación y Carbon en español en AppServiceProvider
# app/Providers/AppServiceProvider.php:
# Paginator::useBootstrapFive();
# Carbon::setLocale('es');

docker exec -it farmacia_php bash
php artisan config:clear && php artisan view:clear && php artisan route:clear && php artisan cache:clear
exit
docker exec -it farmacia_node sh -c "cd /var/www/html && npm run build"
```

Accede en: **http://localhost:8080**  
Usuario: `admin` / Contraseña: `password`

---

## SPRINT 2 — Módulo de Inventario con Lotes (MO_04)

**Objetivo:** CRUD de categorías y productos, gestión de lotes por producto, búsqueda filtrable, kardex, alertas. Implementa CU-05 y CU-06.

### 2.1 — Controladores

```bash
docker exec -it farmacia_php bash
php artisan make:controller CategoriaController --resource
php artisan make:controller ProductoController --resource
php artisan make:controller LoteController
```

**Archivo: `app/Http/Controllers/ProductoController.php`** (CU-05 y CU-06)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\Lote;
use App\Models\KardexProducto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'sesion.activa', 'rol:dueno,vendedor']);
    }

    // CU-06: Consultar catálogo de inventario
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'proveedor'])->activos();

        if ($request->filled('buscar')) {
            $termino = $request->buscar;
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhereHas('categoria', fn($c) => $c->where('nombre', 'like', "%{$termino}%"))
                  ->orWhereHas('proveedor', fn($p) => $p->where('nombre_empresa', 'like', "%{$termino}%"));
            });
        }

        if ($request->filtro === 'critico') {
            $query->whereColumn('stock_total', '<=', 'stock_minimo');
        }

        if ($request->filtro === 'vencer') {
            $query->whereHas('lotes', fn($q) =>
                $q->where('cantidad', '>', 0)
                  ->whereDate('fecha_vencimiento', '<=', now()->addDays(30))
                  ->whereDate('fecha_vencimiento', '>=', now())
            );
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $productos  = $query->orderBy('nombre')->paginate(20)->withQueryString();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.index', compact('productos', 'categorias'));
    }

    // CU-06: Detalle con desglose de lotes
    public function show(int $id)
    {
        $producto = Producto::with(['categoria', 'proveedor', 'lotes' => function ($q) {
            $q->where('cantidad', '>', 0)->orderBy('fecha_vencimiento');
        }])->findOrFail($id);

        $kardex = KardexProducto::with('usuario')
                                ->where('producto_id', $id)
                                ->orderByDesc('fecha_hora')
                                ->paginate(15);

        return view('productos.show', compact('producto', 'kardex'));
    }

    // CU-05: Nuevo producto
    public function create()
    {
        $this->middleware('rol:dueno');
        $categorias  = Categoria::orderBy('nombre')->get();
        $proveedores = Proveedor::activos()->orderBy('nombre_empresa')->get();
        return view('productos.create', compact('categorias', 'proveedores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:150|unique:producto,nombre',
            'categoria_id'    => 'required|exists:categoria,id',
            'proveedor_id'    => 'required|exists:proveedor,id',
            'precio_compra'   => 'required|numeric|min:0',
            'precio_venta'    => 'required|numeric|min:0',
            'stock_minimo'    => 'required|integer|min:0',
            'requiere_receta' => 'boolean',
            // Lote inicial (RF-24, RF-25)
            'numero_lote'       => 'nullable|string|max:50',
            'cantidad_inicial'  => 'nullable|integer|min:0',
            'fecha_vencimiento' => 'nullable|date|after:today',
        ]);

        $producto = Producto::create([
            'nombre'          => $request->nombre,
            'categoria_id'    => $request->categoria_id,
            'proveedor_id'    => $request->proveedor_id,
            'precio_compra'   => $request->precio_compra,
            'precio_venta'    => $request->precio_venta,
            'stock_total'     => 0,
            'stock_minimo'    => $request->stock_minimo,
            'requiere_receta' => $request->boolean('requiere_receta'),
            'estado'          => 'activo',
        ]);

        // Crear lote inicial si se proporcionó
        if ($request->filled('numero_lote') && $request->cantidad_inicial > 0) {
            Lote::create([
                'producto_id'       => $producto->id,
                'numero_lote'       => $request->numero_lote,
                'cantidad'          => $request->cantidad_inicial,
                'fecha_vencimiento' => $request->fecha_vencimiento ?? now()->addYears(2),
                'fecha_ingreso'     => now(),
            ]);

            $producto->recalcularStock();

            KardexProducto::create([
                'producto_id'   => $producto->id,
                'usuario_id'    => auth()->id(),
                'tipo'          => 'entrada',
                'cantidad'      => $request->cantidad_inicial,
                'referencia_id' => null,
                'fecha_hora'    => now(),
            ]);
        }

        return redirect()->route('productos.index')
                         ->with('success', "Producto '{$producto->nombre}' registrado exitosamente.");
    }

    public function edit(int $id)
    {
        $producto    = Producto::findOrFail($id);
        $categorias  = Categoria::orderBy('nombre')->get();
        $proveedores = Proveedor::activos()->orderBy('nombre_empresa')->get();
        return view('productos.edit', compact('producto', 'categorias', 'proveedores'));
    }

    // CU-05 FA_002: Editar producto
    public function update(Request $request, int $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'nombre'          => "required|string|max:150|unique:producto,nombre,{$id}",
            'categoria_id'    => 'required|exists:categoria,id',
            'proveedor_id'    => 'required|exists:proveedor,id',
            'precio_compra'   => 'required|numeric|min:0',
            'precio_venta'    => 'required|numeric|min:0',
            'stock_minimo'    => 'required|integer|min:0',
            'requiere_receta' => 'boolean',
        ]);

        $producto->update($request->only([
            'nombre','categoria_id','proveedor_id',
            'precio_compra','precio_venta','stock_minimo','requiere_receta',
        ]));

        return redirect()->route('productos.show', $id)
                         ->with('success', 'Producto actualizado exitosamente.');
    }

    // CU-05 FA_003: Dar de baja
    public function destroy(int $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update(['estado' => 'inactivo']);
        return redirect()->route('productos.index')
                         ->with('success', "Producto '{$producto->nombre}' dado de baja exitosamente.");
    }

    // Endpoint AJAX para búsqueda en ventas (retorna stock por lotes FEFO)
    public function buscarAjax(Request $request)
    {
        $termino = $request->get('q', '');
        $productos = Producto::activos()
                             ->where(function ($q) use ($termino) {
                                 $q->where('nombre', 'like', "%{$termino}%")
                                   ->orWhereHas('categoria', fn($c) =>
                                       $c->where('nombre', 'like', "%{$termino}%")
                                   );
                             })
                             ->where('stock_total', '>', 0)
                             ->with('categoria')
                             ->limit(10)
                             ->get(['id','nombre','precio_venta','stock_total','requiere_receta','categoria_id']);

        return response()->json($productos);
    }
}
```

**Controlador de Lotes: `app/Http/Controllers/LoteController.php`**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\KardexProducto;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'sesion.activa', 'rol:dueno']);
    }

    // Agregar lote manualmente a un producto
    public function store(Request $request, int $productoId)
    {
        $request->validate([
            'numero_lote'       => 'required|string|max:50',
            'cantidad'          => 'required|integer|min:1',
            'fecha_vencimiento' => 'required|date|after:today',
        ]);

        $producto = Producto::findOrFail($productoId);

        Lote::create([
            'producto_id'       => $productoId,
            'numero_lote'       => $request->numero_lote,
            'cantidad'          => $request->cantidad,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'fecha_ingreso'     => now(),
        ]);

        $producto->recalcularStock();

        KardexProducto::create([
            'producto_id'   => $productoId,
            'usuario_id'    => auth()->id(),
            'tipo'          => 'entrada',
            'cantidad'      => $request->cantidad,
            'referencia_id' => null,
            'fecha_hora'    => now(),
        ]);

        return back()->with('success', 'Lote registrado y stock actualizado.');
    }
}
```

### 2.2 — Rutas de Inventario

```php
// En routes/web.php, dentro del grupo auth:

// Categorías
Route::middleware('rol:dueno,vendedor')->prefix('categorias')->name('categorias.')->group(function () {
    Route::get('/',            [CategoriaController::class, 'index'])->name('index');
    Route::get('/crear',       [CategoriaController::class, 'create'])->name('create')->middleware('rol:dueno');
    Route::post('/',           [CategoriaController::class, 'store'])->name('store')->middleware('rol:dueno');
    Route::get('/{id}/editar', [CategoriaController::class, 'edit'])->name('edit')->middleware('rol:dueno');
    Route::put('/{id}',        [CategoriaController::class, 'update'])->name('update')->middleware('rol:dueno');
});

// Productos
Route::middleware('rol:dueno,vendedor')->prefix('productos')->name('productos.')->group(function () {
    Route::get('/',                 [ProductoController::class, 'index'])->name('index');
    Route::get('/buscar',           [ProductoController::class, 'buscarAjax'])->name('buscar-ajax');
    Route::get('/crear',            [ProductoController::class, 'create'])->name('create')->middleware('rol:dueno');
    Route::post('/',                [ProductoController::class, 'store'])->name('store')->middleware('rol:dueno');
    Route::get('/{id}',             [ProductoController::class, 'show'])->name('show');
    Route::get('/{id}/editar',      [ProductoController::class, 'edit'])->name('edit')->middleware('rol:dueno');
    Route::put('/{id}',             [ProductoController::class, 'update'])->name('update')->middleware('rol:dueno');
    Route::delete('/{id}',          [ProductoController::class, 'destroy'])->name('destroy')->middleware('rol:dueno');
    // Agregar lote a producto
    Route::post('/{id}/lotes',      [LoteController::class, 'store'])->name('lotes.store')->middleware('rol:dueno');
});
```

### 2.3 — Vistas del Inventario

**`resources/views/productos/index.blade.php`** — Tabla filtrable: Nombre · Categoría · Proveedor · Stock Total · Stock Mínimo · Estado · Acciones. Barra de búsqueda + select de categoría + botones de filtro rápido (Todos / Stock Crítico / Próximos a Vencer).

**`resources/views/productos/create.blade.php`** — Formulario con campos base + sección condicional de lote inicial (Número de lote, Cantidad, Fecha de vencimiento). El campo "Requiere receta" es un checkbox.

**`resources/views/productos/edit.blade.php`** — Igual al create pero pre-rellenado. No modifica lotes directamente (los lotes se gestionan desde `show`).

**`resources/views/productos/show.blade.php`** — Tarjeta con info general del producto, tabla de **lotes activos** (Número de lote · Cantidad · Fecha de vencimiento · Días restantes), formulario para agregar nuevo lote, y kardex paginado (Fecha-Hora · Tipo · Cantidad · Usuario).

---

## SPRINT 3 — Módulo de Ventas con FEFO (MO_03)

**Objetivo:** POS con búsqueda AJAX, carrito, verificación de receta, cálculo FEFO para descontar lotes, registro transaccional. Implementa CU-04.

### 3.1 — Lógica FEFO en el Servicio de Ventas

Crea un servicio dedicado para encapsular la lógica de descuento FEFO:

**Archivo: `app/Services/VentaService.php`**

```php
<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\KardexProducto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Receta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VentaService
{
    /**
     * Registra la venta completa usando método FEFO para el descuento de lotes.
     * RF-22: descuenta del lote con fecha de vencimiento más próxima primero.
     */
    public function registrar(array $items, float $montoRecibido): Venta
    {
        return DB::transaction(function () use ($items, $montoRecibido) {
            $total  = 0;
            $lineas = [];

            foreach ($items as $item) {
                $producto = Producto::lockForUpdate()->findOrFail($item['id']);

                // RF-17: Verificar stock total
                if ($producto->stock_total < $item['cant']) {
                    throw new \Exception("Stock insuficiente para: {$producto->nombre}. Disponible: {$producto->stock_total} unidades.");
                }

                // RF-16: Verificar receta
                $recetaId = null;
                if ($producto->requiere_receta) {
                    if (empty($item['receta_folio'])) {
                        throw new \Exception("El producto '{$producto->nombre}' requiere receta médica válida.");
                    }
                    $receta = Receta::where('folio', $item['receta_folio'])
                                    ->where('estado_valida', 'activa')
                                    ->first();
                    if (!$receta) {
                        throw new \Exception("La receta '{$item['receta_folio']}' no es válida o ya fue utilizada.");
                    }
                    $recetaId = $receta->id;
                    $receta->update(['estado_valida' => 'usada']);
                }

                $precioUnitario = $producto->precio_venta;
                $descuento      = $item['desc'] ?? 0;
                $subtotal       = $precioUnitario * $item['cant'] * (1 - $descuento / 100);
                $total         += $subtotal;

                $lineas[] = [
                    'producto'         => $producto,
                    'receta_id'        => $recetaId,
                    'cantidad'         => $item['cant'],
                    'precio_unitario'  => $precioUnitario,
                    'descuento_manual' => $descuento,
                ];
            }

            // Crear la venta
            $venta = Venta::create([
                'vendedor_id' => Auth::id(),
                'folio'       => $this->generarFolio(),
                'fecha_hora'  => now(),
                'total'       => $total,
                'estado'      => 'completada',
            ]);

            // Procesar cada línea con FEFO
            foreach ($lineas as $linea) {
                $producto         = $linea['producto'];
                $cantidadRestante = $linea['cantidad'];

                // FEFO: ordenar lotes por fecha_vencimiento ASC
                $lotes = Lote::where('producto_id', $producto->id)
                             ->where('cantidad', '>', 0)
                             ->orderBy('fecha_vencimiento')
                             ->lockForUpdate()
                             ->get();

                foreach ($lotes as $lote) {
                    if ($cantidadRestante <= 0) break;

                    $descontar = min($lote->cantidad, $cantidadRestante);
                    $lote->decrement('cantidad', $descontar);
                    $cantidadRestante -= $descontar;
                }

                // Recalcular stock_total del producto
                $producto->recalcularStock();

                // Registrar en kardex
                KardexProducto::create([
                    'producto_id'   => $producto->id,
                    'usuario_id'    => Auth::id(),
                    'tipo'          => 'venta',
                    'cantidad'      => -$linea['cantidad'],
                    'referencia_id' => $venta->id,
                    'fecha_hora'    => now(),
                ]);

                // Crear detalle de venta
                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $producto->id,
                    'receta_id'       => $linea['receta_id'],
                    'cantidad'        => $linea['cantidad'],
                    'precio_unitario' => $linea['precio_unitario'],
                    'descuento_manual'=> $linea['descuento_manual'],
                ]);
            }

            return $venta;
        });
    }

    /**
     * Cancela una venta y restaura el stock.
     * NOTA: Al cancelar, el stock se devuelve como ajuste en kardex
     * pero NO se puede reconstruir exactamente qué lote se afectó.
     * Se agrega la cantidad al lote más reciente del producto.
     */
    public function cancelar(Venta $venta): void
    {
        DB::transaction(function () use ($venta) {
            if ($venta->estado === 'cancelada') {
                throw new \Exception('Esta venta ya fue cancelada.');
            }

            foreach ($venta->detalles as $detalle) {
                // Devolver al lote más reciente (o crear ajuste en kardex)
                $producto = $detalle->producto;
                $ultimoLote = Lote::where('producto_id', $producto->id)
                                  ->orderByDesc('fecha_ingreso')
                                  ->first();

                if ($ultimoLote) {
                    $ultimoLote->increment('cantidad', $detalle->cantidad);
                }

                $producto->recalcularStock();

                KardexProducto::create([
                    'producto_id'   => $detalle->producto_id,
                    'usuario_id'    => auth()->id(),
                    'tipo'          => 'devolucion',
                    'cantidad'      => $detalle->cantidad,
                    'referencia_id' => $venta->id,
                    'fecha_hora'    => now(),
                ]);
            }

            $venta->update(['estado' => 'cancelada']);
        });
    }

    private function generarFolio(): string
    {
        $fecha  = now()->format('Ymd');
        $ultimo = Venta::whereDate('fecha_hora', today())->count();
        return 'VTA-' . $fecha . '-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
```

### 3.2 — Controlador de Ventas

```bash
php artisan make:controller VentaController
```

```php
<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function __construct(private VentaService $ventaService)
    {
        $this->middleware(['auth', 'sesion.activa', 'rol:dueno,vendedor']);
    }

    public function index()
    {
        return view('ventas.index');
    }

    public function historial(Request $request)
    {
        $query = Venta::with('vendedor')->orderByDesc('fecha_hora');
        if ($request->filled('fecha_inicio')) $query->whereDate('fecha_hora', '>=', $request->fecha_inicio);
        if ($request->filled('fecha_fin'))    $query->whereDate('fecha_hora', '<=', $request->fecha_fin);
        if ($request->filled('estado'))        $query->where('estado', $request->estado);
        $ventas = $query->paginate(20)->withQueryString();
        return view('ventas.historial', compact('ventas'));
    }

    public function show(int $id)
    {
        $venta = Venta::with(['detalles.producto', 'detalles.receta', 'vendedor'])->findOrFail($id);
        return view('ventas.show', compact('venta'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.id'        => 'required|exists:producto,id',
            'items.*.cant'      => 'required|integer|min:1',
            'items.*.desc'      => 'required|numeric|min:0|max:100',
            'monto_recibido'    => 'required|numeric|min:0',
        ]);

        try {
            $venta = $this->ventaService->registrar($request->items, $request->monto_recibido);
            return response()->json([
                'success'  => true,
                'mensaje'  => 'Venta registrada exitosamente.',
                'venta_id' => $venta->id,
                'redirect' => route('ventas.show', $venta->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cancelar(int $id)
    {
        $this->middleware('rol:dueno');
        try {
            $venta = Venta::with('detalles.producto')->findOrFail($id);
            $this->ventaService->cancelar($venta);
            return back()->with('success', 'Venta cancelada y stock revertido correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
```

### 3.3 — Rutas de Ventas

```php
Route::middleware('rol:dueno,vendedor')->prefix('ventas')->name('ventas.')->group(function () {
    Route::get('/',                    [VentaController::class, 'index'])->name('index');
    Route::post('/',                   [VentaController::class, 'store'])->name('store');
    Route::get('/historial',           [VentaController::class, 'historial'])->name('historial');
    Route::get('/{id}',                [VentaController::class, 'show'])->name('show');
    Route::patch('/{id}/cancelar',     [VentaController::class, 'cancelar'])->name('cancelar')->middleware('rol:dueno');
});
```

### 3.4 — Vista POS

**`resources/views/ventas/index.blade.php`** — Layout de dos columnas:
- **Izquierda:** Input de búsqueda con debounce 300ms (AJAX a `GET /productos/buscar?q=`), lista de resultados, inputs de cantidad y descuento por defecto.
- **Derecha:** Tabla del carrito (Producto · Cant · Precio Unit · Subtotal · Quitar), sección totales, campo "Folio de receta" que aparece solo para productos con `requiere_receta=1`, input monto recibido, cálculo automático de cambio, botón REGISTRAR VENTA (submit AJAX).

El script JavaScript debe:
1. Hacer búsqueda AJAX con debounce de 300ms.
2. Al agregar producto con `requiere_receta=1`, mostrar input de folio de receta en esa fila.
3. Calcular totales en tiempo real.
4. Al confirmar, enviar `POST /ventas` vía fetch con JSON, redirigir al comprobante si `success`.

**`resources/views/ventas/show.blade.php`** — Comprobante con folio en DM Mono, fecha-hora, vendedor, tabla de productos con subtotales y descuentos, total, botón imprimir (`window.print()`).

---

## SPRINT 4 — Módulo de Proveedores y Pedidos con Lotes (MO_05)

**Objetivo:** CRUD de proveedores (con RFC y correo), ciclo completo de pedidos incluyendo recepción con captura de lotes y costos reales. Implementa CU-07 y CU-08.

### 4.1 — Controladores

```bash
php artisan make:controller ProveedorController --resource
php artisan make:controller PedidoController --resource
```

**Lógica clave del `ProveedorController`:**
- `store()`: Valida campos `rfc` (formato RFC mexicano, nullable), `correo_electronico` (email, nullable). Guarda días de visita en tabla `dia_visita_proveedor`.
- `update()`: Actualiza datos + sincroniza días de visita (elimina anteriores y reinserta).
- `destroy()`: Soft-delete lógico (`estado = 'inactivo'`). No elimina si tiene pedidos pendientes.

**Lógica clave del `PedidoController`:**
- `store()`: Crea pedido con folio `PED-YYYYMMDD-NNNN`, estado `pendiente`. Calcula `monto_total` = Σ (precio_compra × cantidad_solicitada).
- `recibirPedido(Request $request, int $id)` (CU-08 FA_002):
  - Actualiza `cantidad_recibida` y `precio_compra_real` en `detalle_pedido`.
  - Para cada producto recibido: crea un nuevo `Lote` con `numero_lote` y `fecha_vencimiento` capturados.
  - Llama a `$producto->recalcularStock()` después de crear cada lote.
  - Registra en `kardex_producto` tipo `entrada` con `referencia_id = pedido_id`.
  - Si `precio_compra_real` difiere del original, actualiza `producto.precio_compra`.
  - Cambia estado del pedido a `recibido`.
- `cancelarPedido(int $id)` (CU-08 FA_003): Cambia estado a `cancelado` sin afectar inventario. Solo pedidos en estado `pendiente`.
- `marcarPagado(Request $request, int $id)` (CU-08 FA_004): Registra `fecha_pago`, cambia estado a `pagado`. Solo pedidos en estado `recibido`.

### 4.2 — Rutas de Proveedores y Pedidos

```php
// Proveedores
Route::middleware('rol:dueno')->prefix('proveedores')->name('proveedores.')->group(function () {
    Route::get('/',              [ProveedorController::class, 'index'])->name('index');
    Route::get('/crear',         [ProveedorController::class, 'create'])->name('create');
    Route::post('/',             [ProveedorController::class, 'store'])->name('store');
    Route::get('/{id}',          [ProveedorController::class, 'show'])->name('show');
    Route::get('/{id}/editar',   [ProveedorController::class, 'edit'])->name('edit');
    Route::put('/{id}',          [ProveedorController::class, 'update'])->name('update');
    Route::delete('/{id}',       [ProveedorController::class, 'destroy'])->name('destroy');
});

// Pedidos
Route::middleware('rol:dueno')->prefix('pedidos')->name('pedidos.')->group(function () {
    Route::get('/',                      [PedidoController::class, 'index'])->name('index');
    Route::get('/crear',                 [PedidoController::class, 'create'])->name('create');
    Route::post('/',                     [PedidoController::class, 'store'])->name('store');
    Route::get('/{id}',                  [PedidoController::class, 'show'])->name('show');
    Route::patch('/{id}/recibir',        [PedidoController::class, 'recibirPedido'])->name('recibir');
    Route::patch('/{id}/cancelar',       [PedidoController::class, 'cancelarPedido'])->name('cancelar');
    Route::patch('/{id}/pagar',          [PedidoController::class, 'marcarPagado'])->name('pagar');
});
```

### 4.3 — Vistas

**`resources/views/proveedores/index.blade.php`** — Tabla: Empresa · Contacto · Teléfono · RFC · Correo · Días de Visita (chips) · Estado · Acciones.

**`resources/views/proveedores/create.blade.php`** — Formulario: nombre empresa, contacto, teléfono, RFC (opcional), correo electrónico (opcional), checkboxes de días de visita (lun–dom).

**`resources/views/pedidos/index.blade.php`** — Tabla con badge de estado (Pendiente/Recibido/Pagado/Cancelado). Filtros por estado y proveedor.

**`resources/views/pedidos/create.blade.php`** — Selector de proveedor + fecha estimada + tabla dinámica de productos (añadir/quitar filas con JS) + cálculo de monto total en tiempo real.

**`resources/views/pedidos/show.blade.php`** — Detalle del pedido. Según estado muestra diferentes acciones:
- Estado `pendiente`: botón "Confirmar Recepción" (despliega formulario con campos `cantidad_recibida`, `precio_compra_real`, `numero_lote`, `fecha_vencimiento` por producto) + botón "Cancelar Pedido".
- Estado `recibido`: botón "Registrar Pago" (modal con campo fecha del pago).
- Estado `pagado` / `cancelado`: solo lectura.

---

## SPRINT 5 — Módulo de Citas Médicas y Agenda (MO_06)

**Objetivo:** Gestión de citas con detección de traslapes, agenda visual, cambio de estado. Implementa CU-09 y CU-10. Accesible para `medico` y `vendedor`.

### 5.1 — Controlador

```bash
php artisan make:controller CitaController
```

**Lógica clave:**
- `store()` (CU-09): Valida `fecha`, `hora`, `motivo`. Verifica traslape: si existe otra cita del mismo médico con el mismo `fecha` y `hora` y estado `programada`, rechaza y muestra "El horario solicitado no está disponible". Sugiere próximos horarios disponibles en los siguientes intervalos de 30 min.
- `update()` (CU-09 FA_002 y FA_003): Maneja reprogramación (`estado = 'reprogramada'`) y cancelación (`estado = 'cancelada'`). Guarda `motivo` del cambio en campo `motivo` de la cita.
- `verificarDisponibilidad()`: Endpoint AJAX que retorna `{disponible: bool, proximos: [lista]}`.

### 5.2 — Rutas

```php
Route::middleware('rol:medico,vendedor,dueno')->prefix('citas')->name('citas.')->group(function () {
    Route::get('/',                          [CitaController::class, 'index'])->name('index');
    Route::get('/crear',                     [CitaController::class, 'create'])->name('create');
    Route::post('/',                         [CitaController::class, 'store'])->name('store');
    Route::get('/{id}',                      [CitaController::class, 'show'])->name('show');
    Route::get('/{id}/editar',               [CitaController::class, 'edit'])->name('edit');
    Route::put('/{id}',                      [CitaController::class, 'update'])->name('update');
    Route::get('/verificar-disponibilidad',  [CitaController::class, 'verificarDisponibilidad'])
         ->name('verificar-disponibilidad');
});
```

### 5.3 — Vistas

**`resources/views/citas/index.blade.php`** (CU-10): Agenda semanal visual con cuadrícula Días × Horas (8:00–20:00). Citas como tarjetas clicables en color accent. Controles `<` `>` para navegar semanas. Botón "Hoy". Botón "+ Nueva Cita". Diferenciación visual entre horarios ocupados y libres.

**`resources/views/citas/create.blade.php`** (CU-09): Formulario: búsqueda de expediente (AJAX) o nombre temporal, fecha, hora (con verificación AJAX de disponibilidad al cambiar), motivo. Muestra sugerencia de próximos horarios disponibles si hay traslape.

---

## SPRINT 6 — Expediente Clínico y Consultas (MO_07)

**Objetivo:** CRUD de expedientes clínicos (con teléfono y correo), registro de consultas médicas, búsqueda por nombre/teléfono. Implementa CU-11 y CU-12.

### 6.1 — Controladores

```bash
php artisan make:controller ExpedienteController --resource
php artisan make:controller ConsultaController --resource
```

**Lógica clave del `ExpedienteController`:**
- `index()` (CU-11 FA_002): Búsqueda por `nombre_completo` o `telefono`. Eager loading de última consulta.
- `show()`: Ficha completa + historial de consultas paginado + lista de recetas.
- `store()` (CU-11): Campos obligatorios: `nombre_completo`, `fecha_nacimiento`, `sexo`. Campos opcionales: `tipo_sangre`, `telefono`, `correo`, `alergias`, `enfermedades_cronicas`, `medicamentos_actuales`, `antecedentes_familiares`.
- `archivar()`: Cambia `estado` a `archivado` (nunca elimina).

**Lógica clave del `ConsultaController`:**
- `store()` (CU-12): Valida `expediente_id`, `motivo`, `diagnostico`, `tipo_consulta`, `estado_pago`. Si tiene `cita_id`, actualiza la cita a `completada`.
- `updateNotas(Request $request, int $id)` (CU-12 FA_002): Actualiza solo el campo `notas_evolucion`.

### 6.2 — Rutas

```php
Route::middleware('rol:medico,dueno')->group(function () {

    // Expedientes (CU-11)
    Route::prefix('expedientes')->name('expedientes.')->group(function () {
        Route::get('/',              [ExpedienteController::class, 'index'])->name('index');
        Route::get('/crear',         [ExpedienteController::class, 'create'])->name('create');
        Route::post('/',             [ExpedienteController::class, 'store'])->name('store');
        Route::get('/{id}',          [ExpedienteController::class, 'show'])->name('show');
        Route::get('/{id}/editar',   [ExpedienteController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [ExpedienteController::class, 'update'])->name('update');
        Route::patch('/{id}/archivar',[ExpedienteController::class, 'archivar'])->name('archivar');
    });

    // Consultas (CU-12)
    Route::prefix('consultas')->name('consultas.')->group(function () {
        Route::get('/nueva',         [ConsultaController::class, 'create'])->name('create');
        Route::post('/',             [ConsultaController::class, 'store'])->name('store');
        Route::get('/{id}',          [ConsultaController::class, 'show'])->name('show');
        Route::get('/{id}/editar',   [ConsultaController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [ConsultaController::class, 'update'])->name('update');
        Route::patch('/{id}/notas',  [ConsultaController::class, 'updateNotas'])->name('update-notas');
    });
});
```

### 6.3 — Vistas

**`resources/views/expedientes/index.blade.php`** — Búsqueda por nombre o teléfono. Tabla: Nombre · Fecha de Nacimiento · Teléfono · Última Consulta · Estado · Acciones.

**`resources/views/expedientes/create.blade.php`** — Dos secciones: "Datos Personales" (nombre, fecha nacimiento, sexo, tipo sangre, teléfono, correo) y "Antecedentes Médicos" (alergias, enfermedades crónicas, medicamentos actuales, antecedentes familiares).

**`resources/views/expedientes/show.blade.php`** — Ficha completa del paciente con edad calculada, secciones colapsables: datos personales, antecedentes, historial de consultas (timeline), recetas emitidas. Botón "Registrar Consulta" destacado.

**`resources/views/consultas/create.blade.php`** — Formulario de dos columnas (diseño del documento):
- **Sección Signos Vitales:** Presión arterial, Temperatura, Frecuencia cardíaca, Peso, Talla.
- **Sección Motivo y Síntomas:** Textareas.
- **Sección Diagnóstico y Tratamiento:** Diagnóstico (obligatorio), Plan de tratamiento/Receta, Estudios solicitados.
- **Sección Cierre de Consulta:** Tipo de consulta (primera vez/seguimiento/urgencia), Costo, Estado de pago (Pagado/Pendiente/Cortesía), Próxima cita sugerida.

---

## SPRINT 7 — Recetas Médicas Digitales (parte de MO_07)

**Objetivo:** Generación de recetas desde consultas, folio único, habilitación automática en ventas, impresión. Implementa CU-13.

### 7.1 — Controlador

```bash
php artisan make:controller RecetaController
```

**Lógica:** 
- `create(int $consultaId)`: Carga datos de consulta y expediente. Permite búsqueda de medicamentos en catálogo (AJAX).
- `store()` (CU-13): Valida al menos un medicamento. Genera folio `REC-YYYYMMDD-NNNN`. Crea `receta` con `estado_valida = 'activa'`. Crea `detalle_receta` por cada medicamento. Si el medicamento existe en catálogo, guarda `producto_id`.
- `imprimir(int $id)`: Vista limpia para impresión con `window.print()`.

### 7.2 — Rutas

```php
Route::middleware('rol:medico,dueno')->prefix('recetas')->name('recetas.')->group(function () {
    Route::get('/consulta/{consultaId}/crear', [RecetaController::class, 'create'])->name('create');
    Route::post('/consulta/{consultaId}',      [RecetaController::class, 'store'])->name('store');
    Route::get('/{id}/imprimir',               [RecetaController::class, 'imprimir'])->name('imprimir');
});
```

### 7.3 — Vista de Impresión

**`resources/views/recetas/imprimir.blade.php`** — Página limpia **sin el layout principal**. Incluye: cabecera con "Farmacia y Consultorio Médico Vida", folio en DM Mono, datos del paciente y médico, tabla de medicamentos (nombre, dosis, frecuencia, duración, indicaciones), espacio para firma. Botón "Imprimir" con `window.print()` y `@media print { .no-print { display: none } }`.

---

## SPRINT 8 — Módulo de Reportes con Exportación PDF (MO_08)

**Objetivo:** Reportes de ventas, inventario y consultas médicas descargables en PDF. Implementa CU-14.

### 8.1 — Instalar dompdf (si no se hizo en Sprint 0)

```bash
docker exec -it farmacia_php bash -c "cd /var/www/html && composer require barryvdh/laravel-dompdf"
```

### 8.2 — Controlador

```bash
php artisan make:controller ReporteController
```

**Archivo: `app/Http/Controllers/ReporteController.php`**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Consulta;
use App\Models\KardexProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'sesion.activa', 'rol:dueno']);
    }

    public function index()
    {
        return view('reportes.index');
    }

    // RF-58: Reporte de ventas
    public function ventas(Request $request)
    {
        $request->validate([
            'periodo'      => 'required|in:dia,semana,mes,rango',
            'fecha_inicio' => 'required_if:periodo,rango|nullable|date',
            'fecha_fin'    => 'required_if:periodo,rango|nullable|date|after_or_equal:fecha_inicio',
        ]);

        [$fechaInicio, $fechaFin] = $this->calcularRango($request);

        $baseQuery = Venta::where('estado', 'completada')
                          ->whereBetween('fecha_hora', [
                              $fechaInicio->startOfDay()->copy(),
                              $fechaFin->endOfDay()->copy(),
                          ]);

        $ingresosTotales     = (clone $baseQuery)->sum('total');
        $numTransacciones    = (clone $baseQuery)->count();
        $promedioPorVenta    = $numTransacciones > 0 ? $ingresosTotales / $numTransacciones : 0;

        $mejorDia = Venta::where('estado', 'completada')
                         ->whereBetween('fecha_hora', [
                             $fechaInicio->copy()->startOfDay(),
                             $fechaFin->copy()->endOfDay(),
                         ])
                         ->select(DB::raw('DAYNAME(fecha_hora) as dia, SUM(total) as total'))
                         ->groupBy('dia')
                         ->orderByDesc('total')
                         ->first();

        // Top 5 productos más vendidos
        $top5Productos = DetalleVenta::select(
                             'producto_id',
                             DB::raw('SUM(cantidad) as unidades_vendidas'),
                             DB::raw('SUM(cantidad * precio_unitario * (1 - descuento_manual/100)) as ingresos')
                         )
                         ->whereHas('venta', fn($q) =>
                             $q->where('estado', 'completada')
                               ->whereBetween('fecha_hora', [
                                   $fechaInicio->copy()->startOfDay(),
                                   $fechaFin->copy()->endOfDay(),
                               ])
                         )
                         ->with('producto.categoria')
                         ->groupBy('producto_id')
                         ->orderByDesc('unidades_vendidas')
                         ->limit(5)
                         ->get();

        $data = compact(
            'ingresosTotales', 'numTransacciones', 'promedioPorVenta',
            'mejorDia', 'top5Productos', 'fechaInicio', 'fechaFin', 'request'
        );

        if ($request->filled('descargar')) {
            $pdf = Pdf::loadView('reportes.ventas-pdf', $data)->setPaper('letter');
            return $pdf->download('reporte-ventas-' . $fechaInicio->format('Y-m-d') . '.pdf');
        }

        return view('reportes.ventas', $data);
    }

    // RF-59: Reporte de consultas médicas
    public function consultas(Request $request)
    {
        $request->validate([
            'periodo'      => 'required|in:dia,semana,mes,rango',
            'fecha_inicio' => 'required_if:periodo,rango|nullable|date',
            'fecha_fin'    => 'required_if:periodo,rango|nullable|date|after_or_equal:fecha_inicio',
        ]);

        [$fechaInicio, $fechaFin] = $this->calcularRango($request);

        $consultas = Consulta::whereBetween('fecha_hora', [
                                  $fechaInicio->startOfDay()->copy(),
                                  $fechaFin->endOfDay()->copy(),
                              ])
                              ->with('expediente', 'medico');

        $totalPacientes     = (clone $consultas)->count();
        $primeraVez         = (clone $consultas)->where('tipo_consulta', 'primera_vez')->count();
        $seguimiento        = (clone $consultas)->where('tipo_consulta', 'seguimiento')->count();
        $urgencias          = (clone $consultas)->where('tipo_consulta', 'urgencia')->count();
        $ingresosTotales    = (clone $consultas)->where('estado_pago', 'pagado')->sum('costo');
        $listadoConsultas   = (clone $consultas)->orderByDesc('fecha_hora')->get();

        $data = compact(
            'totalPacientes', 'primeraVez', 'seguimiento', 'urgencias',
            'ingresosTotales', 'listadoConsultas', 'fechaInicio', 'fechaFin', 'request'
        );

        if ($request->filled('descargar')) {
            $pdf = Pdf::loadView('reportes.consultas-pdf', $data)->setPaper('letter');
            return $pdf->download('reporte-consultas-' . $fechaInicio->format('Y-m-d') . '.pdf');
        }

        return view('reportes.consultas', $data);
    }

    // RF-60: Reporte de inventario
    public function inventario(Request $request)
    {
        // Valoración total del stock
        $valoracionTotal = Producto::activos()
                                   ->select(DB::raw('SUM(stock_total * precio_compra) as total'))
                                   ->value('total') ?? 0;

        // Top 10 más vendidos (por unidades en kardex tipo venta)
        $masVendidos = KardexProducto::where('tipo', 'venta')
                                     ->select('producto_id', DB::raw('SUM(ABS(cantidad)) as total_vendido'))
                                     ->groupBy('producto_id')
                                     ->with('producto.categoria')
                                     ->orderByDesc('total_vendido')
                                     ->limit(10)
                                     ->get();

        // Top 10 menos vendidos
        $menosVendidos = Producto::activos()
                                 ->with('categoria')
                                 ->orderBy('stock_total', 'desc')
                                 ->whereDoesntHave('kardex', fn($q) => $q->where('tipo', 'venta'))
                                 ->orWhereHas('kardex', fn($q) =>
                                     $q->where('tipo', 'venta')
                                       ->groupBy('producto_id')
                                       ->havingRaw('SUM(ABS(cantidad)) < 10')
                                 )
                                 ->limit(10)
                                 ->get();

        $data = compact('valoracionTotal', 'masVendidos', 'menosVendidos');

        if ($request->filled('descargar')) {
            $pdf = Pdf::loadView('reportes.inventario-pdf', $data)->setPaper('letter');
            return $pdf->download('reporte-inventario-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('reportes.inventario', $data);
    }

    private function calcularRango(Request $request): array
    {
        $hoy = now();
        return match ($request->periodo) {
            'dia'   => [$hoy->copy()->startOfDay(), $hoy->copy()->endOfDay()],
            'semana'=> [$hoy->copy()->startOfWeek(), $hoy->copy()->endOfWeek()],
            'mes'   => [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()],
            'rango' => [\Carbon\Carbon::parse($request->fecha_inicio), \Carbon\Carbon::parse($request->fecha_fin)],
            default => [$hoy->copy()->startOfDay(), $hoy->copy()->endOfDay()],
        };
    }
}
```

### 8.3 — Rutas de Reportes

```php
Route::middleware('rol:dueno')->prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/',           [ReporteController::class, 'index'])->name('index');
    Route::get('/ventas',     [ReporteController::class, 'ventas'])->name('ventas');
    Route::get('/consultas',  [ReporteController::class, 'consultas'])->name('consultas');
    Route::get('/inventario', [ReporteController::class, 'inventario'])->name('inventario');
});
```

### 8.4 — Vistas de Reportes

**`resources/views/reportes/index.blade.php`** — Tarjetas de selección de tipo de reporte: Ventas, Consultas Médicas, Inventario.

**`resources/views/reportes/ventas.blade.php`** — Filtros de período + botón "Generar" + botón "Descargar PDF" (`?descargar=1`). Muestra: 4 tarjetas de métricas, tabla Top 5 productos con barra de progreso visual de % del total.

**`resources/views/reportes/ventas-pdf.blade.php`** — Versión limpia del reporte para PDF. Sin layout de app. Incluye cabecera con nombre de farmacia, período del reporte, métricas y tabla.

**`resources/views/reportes/consultas.blade.php`** y **`consultas-pdf.blade.php`** — Igual estructura para reporte de consultas.

**`resources/views/reportes/inventario.blade.php`** y **`inventario-pdf.blade.php`** — Igual estructura para reporte de inventario.

---

## SPRINT 9 — Finalización, Seeders y Pruebas

### 9.1 — Seeders de datos de prueba

```bash
docker exec -it farmacia_php bash
php artisan make:seeder DatabaseSeeder
```

**`database/seeders/DatabaseSeeder.php`:**

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuarios
        DB::table('usuario')->insert([
            ['nombre_completo' => 'Carlos Mendoza', 'username' => 'dueno',    'password_hash' => Hash::make('dueno1234'),    'rol' => 'dueno',    'estado' => 'activo', 'created_at' => now()],
            ['nombre_completo' => 'Ana Ruiz',        'username' => 'vendedor', 'password_hash' => Hash::make('vendedor1234'), 'rol' => 'vendedor', 'estado' => 'activo', 'created_at' => now()],
            ['nombre_completo' => 'Dr. Roberto Silva','username' => 'medico',  'password_hash' => Hash::make('medico1234'),   'rol' => 'medico',   'estado' => 'activo', 'created_at' => now()],
        ]);

        // Categorías
        $categorias = ['Analgésicos', 'Antibióticos', 'Antiinflamatorios', 'Antihistamínicos', 'Vitaminas y Suplementos', 'Respiratorio'];
        foreach ($categorias as $cat) {
            DB::table('categoria')->insert(['nombre' => $cat]);
        }

        // Proveedores
        DB::table('proveedor')->insert([
            'nombre_empresa'     => 'Distribuidora Farmacéutica Nacional',
            'nombre_contacto'    => 'Luis García',
            'telefono'           => '7471234567',
            'rfc'                => 'DFN001231ABC',
            'correo_electronico' => 'ventas@dfn.com',
            'estado'             => 'activo',
        ]);

        DB::table('dia_visita_proveedor')->insert([
            ['proveedor_id' => 1, 'dia_semana' => 'lun'],
            ['proveedor_id' => 1, 'dia_semana' => 'jue'],
        ]);

        // Productos con lotes
        $productos = [
            ['categoria_id' => 1, 'nombre' => 'Paracetamol 500mg',  'precio_compra' => 2.50, 'precio_venta' => 5.00,  'stock_minimo' => 10, 'requiere_receta' => 0],
            ['categoria_id' => 2, 'nombre' => 'Amoxicilina 875mg',  'precio_compra' => 8.00, 'precio_venta' => 12.00, 'stock_minimo' => 5,  'requiere_receta' => 1],
            ['categoria_id' => 1, 'nombre' => 'Ibuprofeno 400mg',   'precio_compra' => 3.00, 'precio_venta' => 6.50,  'stock_minimo' => 15, 'requiere_receta' => 0],
            ['categoria_id' => 5, 'nombre' => 'Vitamina C 1000mg',  'precio_compra' => 4.00, 'precio_venta' => 9.00,  'stock_minimo' => 10, 'requiere_receta' => 0],
        ];

        foreach ($productos as $i => $prod) {
            $pid = DB::table('producto')->insertGetId(array_merge($prod, [
                'proveedor_id' => 1,
                'stock_total'  => 0,
                'estado'       => 'activo',
            ]));

            // Lote de prueba
            $cantLote = ($i === 3) ? 2 : (30 + $i * 15); // Vitamina C con stock bajo para alerta
            DB::table('lote')->insert([
                'producto_id'       => $pid,
                'numero_lote'       => 'LOTE-2024-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'cantidad'          => $cantLote,
                'fecha_vencimiento' => ($i === 3)
                    ? now()->addDays(15)->format('Y-m-d')   // Vitamina C vence en 15 días (alerta)
                    : now()->addMonths(12 + $i)->format('Y-m-d'),
                'fecha_ingreso'     => now()->toDateTimeString(),
            ]);

            DB::table('producto')->where('id', $pid)->update(['stock_total' => $cantLote]);
        }
    }
}
```

```bash
docker exec -it farmacia_php php artisan db:seed
```

### 9.2 — Configuración de AppServiceProvider

**`app/Providers/AppServiceProvider.php`:**

```php
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

public function boot(): void
{
    Paginator::useBootstrapFive();
    Carbon::setLocale('es');
    \Carbon\CarbonImmutable::setLocale('es');
}
```

### 9.3 — Manejo global de errores

Crea `resources/views/errors/403.blade.php`, `404.blade.php`, `500.blade.php` extendiendo `layouts.app` con mensajes amigables en español.

### 9.4 — Optimización para producción

```bash
docker exec -it farmacia_php bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
exit
docker exec -it farmacia_node sh -c "cd /var/www/html && npm run build"
```

### 9.5 — Checklist de pruebas funcionales

**MO_01 — Autenticación (CU-01):**
- [ ] Login con credenciales correctas → redirige al dashboard según rol
- [ ] Login con credenciales incorrectas → "Nombre de usuario o contraseña incorrectos. Intente de nuevo."
- [ ] 20 minutos de inactividad → "Su sesión expiró por inactividad. Inicie sesión nuevamente."
- [ ] Cierre de sesión manual → redirige a login

**MO_02 — Panel (CU-02):**
- [ ] Dueño ve: ventas, transacciones, total productos, alertas
- [ ] Vendedor ve: ventas, transacciones, alertas (sin total productos)
- [ ] Médico ve: citas hoy y consultas hoy
- [ ] Sección Stock Crítico muestra productos con stock_total ≤ stock_minimo
- [ ] Sección Próximos a Vencer muestra lotes con fecha_vencimiento ≤ 30 días

**MO_03 — Ventas (CU-04):**
- [ ] Búsqueda AJAX devuelve resultados
- [ ] Producto con receta exige folio de receta médica
- [ ] Stock insuficiente → "Stock insuficiente. Disponible: X unidades."
- [ ] Venta completa → comprobante con folio único
- [ ] FEFO: el lote con menor fecha de vencimiento se descuenta primero
- [ ] Cancelación de venta → stock revertido + kardex "devolución"

**MO_04 — Inventario (CU-05, CU-06):**
- [ ] Crear producto con lote inicial → aparece en kardex como "entrada"
- [ ] Ver detalle producto → muestra desglose de lotes activos con cantidades
- [ ] Agregar lote adicional → stock_total se recalcula correctamente
- [ ] Dar de baja producto → desaparece de búsqueda en ventas

**MO_05 — Proveedores y Pedidos (CU-07, CU-08):**
- [ ] Crear proveedor con RFC y correo
- [ ] Crear pedido → estado "pendiente" + folio único
- [ ] Confirmar recepción → crea lotes + stock_total actualizado + kardex "entrada"
- [ ] Diferencia en cantidad recibida → muestra notificación y registra cantidad real
- [ ] Cancelar pedido → estado "cancelado" sin afectar inventario
- [ ] Marcar pagado → fecha_pago registrada

**MO_06 — Citas (CU-09, CU-10):**
- [ ] Crear cita en horario disponible → estado "programada"
- [ ] Crear cita en horario ocupado → error + sugerencia de próximos horarios
- [ ] Reprogramar cita → estado "reprogramada" + nuevo horario
- [ ] Cancelar cita → estado "cancelada" + motivo registrado
- [ ] Agenda semanal muestra citas por fecha y hora con diferenciación visual

**MO_07 — Expediente y Consultas (CU-11, CU-12, CU-13):**
- [ ] Crear expediente con teléfono y correo
- [ ] Buscar expediente por nombre o teléfono
- [ ] Registrar consulta → se vincula a expediente y cita (si aplica)
- [ ] Consulta registrada → cita se marca automáticamente como "completada"
- [ ] Generar receta → folio único + medicamentos vinculados
- [ ] Receta con estado "activa" es visible en módulo de ventas
- [ ] Imprimir receta → página limpia sin layout de app

**MO_08 — Reportes (CU-14):**
- [ ] Reporte de ventas por período muestra ingresos, transacciones y top 5
- [ ] Reporte de consultas muestra tipos y total de ingresos
- [ ] Reporte de inventario muestra valoración y top 10 más/menos vendidos
- [ ] Todos los reportes se descargan correctamente en PDF

---

## RESUMEN DE ARCHIVOS A CREAR

```
src/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── UsuarioController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ProductoController.php
│   │   │   ├── LoteController.php
│   │   │   ├── CategoriaController.php
│   │   │   ├── ProveedorController.php
│   │   │   ├── PedidoController.php
│   │   │   ├── VentaController.php
│   │   │   ├── CitaController.php
│   │   │   ├── ExpedienteController.php
│   │   │   ├── ConsultaController.php
│   │   │   ├── RecetaController.php
│   │   │   └── ReporteController.php
│   │   └── Middleware/
│   │       ├── VerificarRol.php
│   │       └── SesionActiva.php
│   ├── Models/
│   │   ├── Usuario.php
│   │   ├── Categoria.php
│   │   ├── Producto.php
│   │   ├── Lote.php              ← NUEVO
│   │   ├── Proveedor.php         ← actualizado (rfc, correo)
│   │   ├── DiaVisitaProveedor.php
│   │   ├── KardexProducto.php
│   │   ├── Pedido.php            ← actualizado (estado cancelado)
│   │   ├── DetallePedido.php     ← actualizado (precio_compra_real)
│   │   ├── Venta.php
│   │   ├── DetalleVenta.php
│   │   ├── Receta.php
│   │   ├── DetalleReceta.php
│   │   ├── ExpedienteClinico.php ← actualizado (telefono, correo)
│   │   ├── Cita.php
│   │   └── Consulta.php
│   └── Services/
│       └── VentaService.php      ← NUEVO (lógica FEFO)
├── resources/views/
│   ├── layouts/app.blade.php
│   ├── auth/login.blade.php
│   ├── dashboard.blade.php
│   ├── usuarios/{index,create,edit,cambiar-password}.blade.php
│   ├── categorias/{index,create,edit}.blade.php
│   ├── productos/{index,create,edit,show}.blade.php
│   ├── proveedores/{index,create,edit,show}.blade.php
│   ├── pedidos/{index,create,show}.blade.php
│   ├── ventas/{index,show,historial}.blade.php
│   ├── citas/{index,create,edit,show}.blade.php
│   ├── expedientes/{index,create,edit,show}.blade.php
│   ├── consultas/{create,edit,show}.blade.php
│   ├── recetas/{create,imprimir}.blade.php
│   ├── reportes/{index,ventas,ventas-pdf,consultas,consultas-pdf,inventario,inventario-pdf}.blade.php
│   └── errors/{403,404,500}.blade.php
├── routes/web.php
├── database/seeders/DatabaseSeeder.php
└── config/auth.php
```

---

## GUÍA DE ESTILOS — APLICAR EN TODAS LAS VISTAS

| Elemento | Clase/Valor |
|----------|-------------|
| Contenedor | `<div class="card">` |
| Encabezado de sección | `<div class="card-header">` con Outfit font |
| Tablas | `<table class="table table-hover">` |
| Botón principal | `<button class="btn btn-accent">` + icono `save` |
| Botón editar | `<button class="btn btn-sm btn-outline-secondary">` + icono `pencil` |
| Botón eliminar/baja | `<button class="btn btn-sm">` con `color:var(--color-danger)` + icono `trash-2` |
| Botón nuevo | `<button class="btn btn-accent">` + icono `plus` |
| Badge activo | `<span class="badge badge-activo">Activo</span>` |
| Badge inactivo | `<span class="badge badge-inactivo">Inactivo</span>` |
| Badge pendiente | `<span class="badge badge-pendiente">Pendiente</span>` |
| Badge recibido | `<span class="badge badge-recibido">Recibido</span>` |
| Badge pagado | `<span class="badge badge-pagado">Pagado</span>` |
| Badge cancelado | `<span class="badge badge-cancelado">Cancelado</span>` |
| Folios/códigos | `<code class="folio">VTA-20241024-0001</code>` |
| Error de campo | `@error('campo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror` |
| Input con error | `class="form-control @error('campo') is-invalid @enderror"` |
| Paginación | `{{ $items->links() }}` (Bootstrap 5 configurado en AppServiceProvider) |

---

## CREDENCIALES DE ACCESO INICIALES

| Rol | Usuario | Contraseña | Módulos accesibles |
|-----|---------|------------|--------------------|
| Dueño | `admin` | `password` | TODOS |
| Dueño (prueba) | `dueno` | `dueno1234` | TODOS |
| Vendedor | `vendedor` | `vendedor1234` | Panel · Ventas · Inventario (consulta) · Citas |
| Médico | `medico` | `medico1234` | Panel · Citas · Expedientes · Consultas · Recetas |

> ⚠️ **Cambia las contraseñas predeterminadas inmediatamente después del primer acceso.**

---

## VARIABLES DE ENTORNO `.env`

```env
APP_NAME="Farmacia Vida"
APP_ENV=local
APP_KEY=                   # Generado con php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8080
APP_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=farmacia_vida
DB_USERNAME=farmacia_user
DB_PASSWORD=farmacia_pass

SESSION_DRIVER=database
SESSION_LIFETIME=20

CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
MAIL_MAILER=log
```

---

## COMANDOS DOCKER DE REFERENCIA RÁPIDA

```bash
# Arrancar
docker-compose up -d

# Entrar a PHP
docker exec -it farmacia_php bash

# Entrar a Node
docker exec -it farmacia_node sh

# Limpiar caché Laravel
php artisan config:clear && php artisan view:clear && php artisan route:clear && php artisan cache:clear

# Ver logs
tail -f storage/logs/laravel.log

# Ver rutas registradas
php artisan route:list

# Ejecutar seeders
php artisan db:seed

# Compilar assets
docker exec -it farmacia_node sh -c "cd /var/www/html && npm run build"

# MySQL directo
docker exec -it farmacia_mysql mysql -u farmacia_user -pfarmacia_pass farmacia_vida
```

---

*Plan de desarrollo v2.0 — Basado en: Fase de Análisis actualizada (módulos MO_01–MO_08, RF-01–RF-60, RNF-01–RNF-21, CU-01–CU-14) y script SQL v2 con tabla `lote`, campos `rfc`/`correo_electronico` en proveedor, `stock_total` en producto, `precio_compra_real` en detalle_pedido. Sistema de Gestión Integral para la Farmacia y Consultorio Médico "Vida" — Tecnológico Nacional de México, Instituto Tecnológico de Chilpancingo.*
