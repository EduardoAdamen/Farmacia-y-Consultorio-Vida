# 💊 Farmacia y Consultorio Vida

Sistema web integral y profesional para la gestión de farmacias con consultorio médico integrado. Permite automatizar de extremo a extremo la atención médica, la emisión de recetas, el control estricto de inventarios por lotes con caducidades (FEFO), la venta y la generación de reportes gerenciales.

[![Laravel Version](https://img.shields.io/badge/Laravel-13.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.0-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Docker Support](https://img.shields.io/badge/Docker-Enabled-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Production_Ready-success?style=for-the-badge)](#)

---

## 📝 Descripción General

**Farmacia y Consultorio Vida** es un sistema web monolítico moderno que unifica dos áreas críticas de un establecimiento de salud comunitaria: **la consulta médica** y **la dispensación de medicamentos**. 

Tradicionalmente, las farmacias y los consultorios médicos adyacentes operan de forma aislada, lo que provoca duplicidad de trabajo, errores en la interpretación de recetas escritas a mano y una nula trazabilidad de inventarios. Este sistema resuelve dicha problemática proporcionando un expediente clínico digitalizado para el médico, un flujo automático de emisión de recetas con códigos internos que la farmacia puede validar y surtir con un solo clic, y un control exhaustivo de lotes para evitar que se vendan medicamentos caducados.

---

## ✨ Características Principales

### 👥 Gestión de Usuarios y Roles (RBAC)
*   **Tres Perfiles Operativos**:
    *   **Dueño (Administrador)**: Acceso total al sistema, reportes financieros, auditoría de Kardex, gestión de compras a proveedores, control de usuarios y cancelación de ventas.
    *   **Médico**: Gestión de expedientes clínicos, agenda de citas, realización de consultas médicas y generación de recetas electrónicas.
    *   **Vendedor (Cajero)**: Operación de la caja registradora, venta al público, verificación de recetas controladas y agendamiento de citas.
*   **Seguridad y Sesiones**: Middleware personalizado para verificar la vigencia de la sesión (`SesionActiva`) y denegar el acceso a rutas no autorizadas por rol (`VerificarRol`).

### 📂 Expediente Clínico Digital
*   Registro estructurado del paciente: alergias, enfermedades crónicas, medicamentos actuales, tipo de sangre y antecedentes familiares.
*   Historial clínico unificado con todas las consultas médicas del paciente.
*   Función de **Archivado/Desarchivado** para mantener limpia la lista de pacientes activos sin perder sus registros históricos.

### 🩺 Consultas Médicas y Recetas
*   Toma y registro de signos vitales (presión arterial, temperatura, frecuencia cardíaca, peso, talla).
*   Diagnóstico, tratamiento y notas de evolución.
*   **Recetario Electrónico**: Generación automática de recetas digitales con folio único (`REC-YYYYMMDD-XXXX`).
*   **Impresión PDF**: Formato listo para imprimir con indicaciones personalizadas por medicamento usando `laravel-dompdf`.

### 📦 Inventario Inteligente (Kardex y FEFO)
*   **Descuento FEFO (First Expired, First Out)**: Las ventas descuentan automáticamente del lote cuya fecha de caducidad sea la más próxima.
*   **Gestión de Lotes**: Control de cantidades individuales por lote, fechas de vencimiento y alertas visuales de productos por caducar.
*   **Registro de Kardex**: Bitácora histórica inmutable de cada movimiento de inventario (Entrada, Salida, Ajuste, Venta, Devolución) con el usuario responsable.
*   **Stock Mínimo**: Sistema de alarmas para productos con existencias por debajo del límite crítico.

### 🛒 Caja y Punto de Venta (POS)
*   Carrito de compras interactivo con búsqueda en tiempo real de productos mediante AJAX (nombre, SKU o código de barras).
*   **Validación Estricta de Recetas**: Impide la venta de medicamentos que requieren receta a menos que se ingrese un folio válido (interno o externo).
*   Descuentos manuales por línea de venta con límites porcentuales.
*   Cancelación de ventas (exclusivo para Dueño) con devolución automática al lote más reciente de la farmacia y registro de Kardex de devolución.

### 🚛 Compras y Pedidos a Proveedores
*   Registro de pedidos a proveedores con folio único y fecha estimada de entrega.
*   Control de estados: `Pendiente`, `Recibido`, `Pagado` y `Cancelado`.
*   **Procesamiento de Recepción**: Incrementa automáticamente el stock de productos, crea el lote correspondiente y genera el movimiento de entrada en Kardex en una transacción atómica.
*   Registro de días de visita asignados por proveedor para optimizar el calendario de compras.

### 📊 Reportes y Auditoría
*   **Reporte de Ventas**: Visualización de ingresos totales, número de transacciones, promedio por venta, mejor día de venta de la semana y Top 5 de productos más vendidos. Exportable en formato PDF.
*   **Reporte de Consultas**: Métrica de pacientes atendidos (primera vez vs. seguimiento vs. urgencia), ingresos por honorarios médicos y listado detallado de consultas. Exportable en PDF.
*   **Reporte de Inventarios**: Valoración total del inventario a precio de compra, listado de los 10 productos más vendidos y los 10 productos con menor rotación. Exportable en PDF.

---

## 🛠️ Stack Tecnológico

El sistema se basa en tecnologías modernas de código abierto seleccionadas por su estabilidad, rendimiento y facilidad de mantenimiento.

| Componente | Tecnología | Versión / Tipo | Uso en el Sistema |
| :--- | :--- | :--- | :--- |
| **Backend Framework** | Laravel | `^13.0` | Arquitectura MVC, enrutamiento, ORM Eloquent y API. |
| **Runtime Environment**| PHP | `^8.3` | Motor de ejecución del servidor backend. |
| **Frontend Styling** | Tailwind CSS | `^4.0.0` (con `@tailwindcss/vite`) | Diseño de interfaz de usuario moderno y responsivo. |
| **Asset Builder** | Vite | `^8.0.0` | Compilación ultrarrápida de scripts y hojas de estilo. |
| **Database** | MySQL | `8.0` | Motor de base de datos relacional y transaccional. |
| **Database ORM** | Eloquent ORM | Nativo de Laravel | Mapeo objeto-relacional y control de consultas. |
| **PDF Engine** | Laravel-DOMPDF | `^3.1` | Generación dinámica de recetas e informes en PDF. |
| **Web Server** | Nginx | Alpine | Servidor web inverso y distribuidor de archivos estáticos. |
| **Container Engine** | Docker & Docker Compose | Versión de esquema `3.8` | Virtualización y consistencia del entorno local y prod. |
| **Node.js (Build)** | Node.js | `20-alpine` | Entorno para instalación y compilación de dependencias frontend. |

---

## 📐 Arquitectura del Sistema

El sistema implementa una **Arquitectura Monolítica Modular** siguiendo el patrón **MVC (Modelo-Vista-Controlador)** provisto por Laravel.

### Características Clave de Diseño:
1.  **Transaccionalidad Atómica**: Toda operación crítica (como registrar una venta o recibir un pedido de proveedor) está envuelta en un bloque `DB::transaction()` para asegurar que no queden datos inconsistentes en caso de fallos intermedios.
2.  **Manejo de Concurrencia (Locking)**: El módulo de ventas bloquea las filas de productos involucrados utilizando `lockForUpdate()` para prevenir condiciones de carrera cuando múltiples vendedores realizan transacciones en paralelo.
3.  **Seguridad por Capas**:
    *   **Capa de Autenticación**: Middleware global `auth`.
    *   **Capa de Control de Sesión**: Middleware `SesionActiva` que invalida sesiones que no coinciden con el estado en base de datos.
    *   **Capa de Roles**: Middleware `VerificarRol` que restringe el acceso a nivel de ruta y controladores.

---

## 📁 Estructura del Proyecto

A continuación se muestra el árbol de directorios principal simplificado de la aplicación:

```directory
farmacia-vida/
├── .env.aws.example           # Configuración de variables de entorno para producción en AWS EC2.
├── docker-compose.yml         # Orquestador de contenedores para entorno de desarrollo local.
├── docker-compose.prod.yml    # Orquestador de contenedores optimizado para producción.
├── setup.sh                   # Script de automatización de instalación local.
├── mysql/
│   └── init.sql               # Script SQL de creación de base de datos, tablas, índices y admin.
├── nginx/
│   └── default.conf           # Configuración de bloques de servidor de Nginx.
├── php/
│   ├── Dockerfile             # Multi-stage Dockerfile para PHP-FPM y compilación de Node.
│   ├── docker-entrypoint.sh   # Script de arranque del contenedor (espera DB y corre migraciones).
│   └── conf.d/
│       └── production.ini     # Configuraciones optimizadas de PHP para producción (OPcache, etc).
└── src/                       # Directorio raíz del proyecto Laravel
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/   # Controladores (Cita, Consulta, Venta, Expediente, etc.)
    │   │   └── Middleware/    # Middlewares (VerificarRol, SesionActiva)
    │   ├── Models/            # Modelos Eloquent de base de datos
    │   └── Services/          # Servicios lógicos (VentaService - FEFO)
    ├── bootstrap/
    │   └── app.php            # Configuración de middlewares y bootstrap de Laravel 13
    ├── config/                # Configuraciones del framework
    ├── database/
    │   ├── migrations/        # Migraciones suplementarias (SKU, pedidos, etc.)
    │   └── seeders/           # Sembradores de datos de demostración (DatabaseSeeder)
    ├── public/                # Directorio público (Assets compilados por Vite)
    ├── resources/
    │   ├── views/             # Vistas del sistema (Plantillas Blade)
    │   └── css/               # Archivos CSS globales
    ├── routes/
    │   └── web.php            # Rutas de la interfaz web del sistema
    ├── package.json           # Dependencias frontend (Tailwind v4, Vite)
    └── composer.json          # Dependencias PHP (Laravel Framework, Barryvdh-DOMPDF)
```

---

## ⚙️ Requisitos Previos

Si deseas ejecutar el proyecto directamente en tu sistema o mediante contenedores, se recomiendan las siguientes versiones mínimas:

*   **Docker Engine**: `v24.0` o superior
*   **Docker Compose**: `v2.20` o superior
*   **Si prefieres ejecución nativa sin Docker**:
    *   **PHP**: `^8.3` (con extensiones: `bcmath, gd, mbstring, pdo_mysql, zip, xml`)
    *   **MySQL**: `^8.0`
    *   **Node.js**: `^20.0`
    *   **Composer**: `^2.6`

---

## 🚀 Instalación y Configuración

### Método Rápido (Recomendado con Docker y Setup Automático)

El proyecto incluye un script de bash (`setup.sh`) que automatiza por completo la puesta en marcha en sistemas Unix/Linux o Git Bash en Windows.

1.  **Clonar el repositorio**:
    ```bash
    git clone https://github.com/EduardoAdamen/Farmacia-y-Consultorio-Vida.git
    cd farmacia-vida
    ```

2.  **Ejecutar el script de instalación**:
    ```bash
    chmod +x setup.sh
    ./setup.sh
    ```
    *Este script realizará las siguientes operaciones de forma automática:*
    *   Creará el archivo `./src/.env` a partir de `./src/.env.example`.
    *   Construirá y levantará los contenedores de desarrollo en segundo plano.
    *   Instalará las dependencias de Composer.
    *   Generará la clave de la aplicación (`APP_KEY`).
    *   Configurará los permisos correctos para las carpetas `storage` y `bootstrap/cache`.
    *   Ejecutará el script `init.sql` en MySQL y correrá las migraciones adicionales con datos de prueba (`db:seed`).
    *   Instalará los módulos de Node y compilará los recursos con Vite.
    *   Limpiará y optimizará la caché de Laravel.

3.  **Acceder al sistema**:
    Abre tu navegador e ingresa a: **[http://localhost:8080](http://localhost:8080)**

---

### Método Manual (Sin Docker)

Si prefieres levantar el proyecto de manera tradicional en tu servidor web local (como Laragon, XAMPP o PHP+MySQL instalado manualmente):

1.  **Configurar base de datos**:
    Crea una base de datos MySQL llamada `farmacia_vida` e importa el archivo `./mysql/init.sql` para tener la estructura y datos de inicio:
    ```bash
    mysql -u tu_usuario -p farmacia_vida < mysql/init.sql
    ```

2.  **Copiar y ajustar el archivo de entorno**:
    ```bash
    cp src/.env.example src/.env
    ```
    *Edita `src/.env` y coloca las credenciales de tu servidor de base de datos local.*

3.  **Instalar dependencias de PHP y generar la llave**:
    ```bash
    cd src
    composer install
    php artisan key:generate
    ```

4.  **Ejecutar migraciones y seeders adicionales**:
    ```bash
    php artisan migrate --seed
    ```

5.  **Instalar dependencias de Node y compilar**:
    ```bash
    npm install
    npm run build
    ```

6.  **Iniciar servidor de desarrollo**:
    ```bash
    php artisan serve
    ```

---

## 🔒 Variables de Entorno

El sistema requiere la definición de variables de entorno para configurar adecuadamente las conexiones y seguridad. A continuación se listan las más relevantes:

| Variable | Tipo | Valor por Defecto | Descripción |
| :--- | :--- | :--- | :--- |
| `APP_NAME` | Texto | `"Farmacia y Consultorio Vida"` | Nombre de la aplicación que se muestra en los títulos y correos. |
| `APP_ENV` | Texto | `local` / `production` | Define el comportamiento del entorno (errores detallados o silencioso). |
| `APP_KEY` | String | `base64:...` | Clave criptográfica para encriptar sesiones y datos (generada con `key:generate`). |
| `APP_DEBUG` | Booleano| `true` (dev) / `false` (prod)| Habilita o deshabilita la pantalla de depuración detallada. |
| `APP_URL` | URL | `http://localhost:8080` | URL base utilizada para generar enlaces absolutos. |
| `DB_CONNECTION`| Texto | `mysql` | Controlador de base de datos a utilizar. |
| `DB_HOST` | Host | `mysql` (Docker) / `127.0.0.1` | Dirección IP o dominio del servidor de base de datos. |
| `DB_PORT` | Puerto | `3306` | Puerto de conexión de la base de datos MySQL. |
| `DB_DATABASE` | Texto | `farmacia_vida` | Nombre de la base de datos. |
| `DB_USERNAME` | Texto | `farmacia_user` | Usuario con privilegios en la base de datos. |
| `DB_PASSWORD` | String | *Vacío* / `farmacia_pass` | Contraseña del usuario de base de datos. |
| `SESSION_DRIVER`| Texto | `file` | Driver para almacenar las sesiones de usuario activos. |
| `CACHE_STORE` | Texto | `file` | Driver para el sistema de almacenamiento en caché. |
| `QUEUE_CONNECTION`| Texto| `database` / `sync` | Driver de cola para procesamiento en segundo plano. |
| `RUN_MIGRATIONS`| Booleano| `false` | (Solo en prod) Fuerza la ejecución automática de migraciones al arrancar. |
| `RUN_SEEDERS` | Booleano| `false` | (Solo en prod) Fuerza la ejecución de seeders iniciales al arrancar. |

---

## 📜 Scripts Disponibles

El proyecto cuenta con scripts simplificados dentro del archivo `composer.json` y `package.json` para facilitar las tareas cotidianas de desarrollo.

### Scripts de Composer (`src/composer.json`)
*   **`composer run setup`**: Ejecuta el pipeline completo de configuración inicial de Laravel (instala composer, copia `.env`, genera llave, migra base de datos, instala npm y construye con vite).
*   **`composer run dev`**: Comando ultra-optimizado que ejecuta concurrentemente en una sola terminal:
    *   El servidor web interno de Laravel (`php artisan serve`).
    *   El escuchador de colas en segundo plano (`php artisan queue:listen`).
    *   El monitor de logs Pail (`php artisan pail`).
    *   El servidor de desarrollo de Vite (`npm run dev`).
*   **`composer run test`**: Limpia la caché de configuración y ejecuta el conjunto de pruebas unitarias y de integración de la aplicación.

### Scripts de Node (`src/package.json`)
*   **`npm run dev`**: Arranca el servidor de desarrollo de Vite para compilar recursos frontend al vuelo con TailwindCSS v4.
*   **`npm run build`**: Compila, minifica y optimiza todos los archivos CSS, JavaScript e imágenes para su despliegue final en producción.

---

## 🛣️ API y Endpoints

El sistema opera bajo rutas web protegidas por autenticación y asignación de roles. El acceso general está unificado bajo el middleware de sesión activa.

### 🔐 Rutas de Acceso Público
| Método | Endpoint | Acción / Controlador | Función |
| :--- | :--- | :--- | :--- |
| **GET** | `/` o `/login` | `LoginController@showLoginForm` | Muestra la pantalla de inicio de sesión. |
| **POST** | `/login` | `LoginController@login` | Valida credenciales e inicia sesión. |
| **POST** | `/logout` | `LoginController@logout` | Cierra la sesión activa del usuario. |

### 🛠️ Área Autenticada (Comunes)
Rutas accesibles por cualquier usuario autenticado con sesión activa (`auth`, `sesion.activa`):
| Método | Endpoint | Acción / Controlador | Función |
| :--- | :--- | :--- | :--- |
| **GET** | `/panel-inicio` | `PanelController@index` | Dashboard principal adaptado a las métricas del rol de usuario. |
| **GET** | `/perfil/password` | `UsuarioController@showCambiarPassword`| Vista de cambio de contraseña personal. |
| **PATCH** | `/perfil/password` | `UsuarioController@cambiarPassword` | Procesa y actualiza la contraseña con hash. |

### 👑 Rutas de Administración (Solo `Dueño`)
Protegidas por el middleware `rol:dueno`.
| Método | Endpoint | Acción / Controlador | Función |
| :--- | :--- | :--- | :--- |
| **GET** | `/usuarios` | `UsuarioController@index` | Lista todos los usuarios registrados. |
| **GET** | `/usuarios/crear` | `UsuarioController@create` | Formulario de registro de nuevo usuario. |
| **POST** | `/usuarios` | `UsuarioController@store` | Guarda el nuevo usuario en base de datos. |
| **GET** | `/usuarios/{id}/editar` | `UsuarioController@edit` | Vista de edición de usuario. |
| **PUT** | `/usuarios/{id}` | `UsuarioController@update` | Actualiza la información básica del usuario. |
| **PATCH** | `/usuarios/{id}/estado` | `UsuarioController@toggleEstado` | Activa o inactiva un usuario (Bloqueo de acceso). |
| **PATCH** | `/usuarios/{id}/reset` | `UsuarioController@resetPassword` | Restablece la contraseña de un usuario a la por defecto. |
| **DELETE** | `/proveedores/{id}` | `ProveedorController@destroy` | Elimina lógicamente un proveedor. |
| **PATCH** | `/pedidos/{id}/recibir` | `PedidoController@recibirPedido` | Recibe mercancía, crea lotes y actualiza stock. |
| **PATCH** | `/pedidos/{id}/cancelar` | `PedidoController@cancelarPedido` | Cancela un pedido pendiente. |
| **PATCH** | `/pedidos/{id}/pagar` | `PedidoController@marcarPagado` | Registra la fecha de pago del pedido al proveedor. |
| **PATCH** | `/ventas/{id}/cancelar` | `VentaController@cancelar` | Cancela venta, restaura stock con FEFO inverso y Kardex. |
| **GET** | `/reportes` | `ReporteController@index` | Menú de selección de reportes. |
| **GET** | `/reportes/ventas` | `ReporteController@ventas` | Reporte detallado y descarga de PDF de ventas. |
| **GET** | `/reportes/consultas` | `ReporteController@consultas` | Reporte y PDF de ingresos de consultorio. |
| **GET** | `/reportes/inventario` | `ReporteController@inventario` | Reporte y PDF de valoración y rotación de stock. |

### 📦 Rutas de Catálogos y POS (`Dueño` y `Vendedor`)
Accesibles mediante el middleware `rol:dueno,vendedor`.
| Método | Endpoint | Acción / Controlador | Función |
| :--- | :--- | :--- | :--- |
| **GET** | `/categorias` | `CategoriaController@index` | Catálogo de categorías. |
| **GET** | `/productos` | `ProductoController@index` | Lista de productos con buscador y stock total. |
| **GET** | `/productos/{id}` | `ProductoController@show` | Ficha técnica del producto y desglose de lotes activos. |
| **GET** | `/ventas` | `VentaController@index` | Interfaz del punto de venta (POS) con carrito dinámico. |
| **POST** | `/ventas` | `VentaController@store` | Registra transaccionalmente la venta de los productos. |
| **GET** | `/ventas/historial` | `VentaController@historial` | Bitácora histórica de tickets de venta emitidos. |

### 🩺 Rutas del Consultorio Médico (`Médico` y `Dueño`)
Accesibles mediante middleware `rol:medico,dueno`.
*Las acciones de escritura (`create`, `store`, `edit`, `update`, `archivar`) están limitadas estrictamente al rol `medico` mediante validación interna en el controlador.*
| Método | Endpoint | Acción / Controlador | Función |
| :--- | :--- | :--- | :--- |
| **GET** | `/expedientes` | `ExpedienteController@index` | Buscador de expedientes de pacientes. |
| **GET** | `/expedientes/crear` | `ExpedienteController@create` | Apertura de nuevo expediente clínico. |
| **POST** | `/expedientes` | `ExpedienteController@store` | Guarda los datos del paciente en base de datos. |
| **GET** | `/expedientes/{id}` | `ExpedienteController@show` | Ficha médica del paciente y su historial de consultas. |
| **PATCH**| `/expedientes/{id}/archivar` | `ExpedienteController@archivar` | Archiva temporalmente el expediente del paciente. |
| **GET** | `/consultas/nueva` | `ConsultaController@create` | Interfaz de captura de consulta y signos vitales. |
| **POST** | `/consultas` | `ConsultaController@store` | Registra el diagnóstico, tratamiento y receta médica. |
| **GET** | `/recetas/{id}/imprimir` | `RecetaController@imprimir` | Genera y transmite el PDF de la receta para su impresión. |

---

## 🗄️ Base de Datos y Modelo de Datos

El motor seleccionado es **MySQL 8.0** configurado bajo el estándar de almacenamiento **InnoDB** para soportar transacciones ACID e integridad referencial estricta.

### Diagrama Simplificado de Relaciones de la Base de Datos


### Índices de Rendimiento Críticos (Incluidos en `init.sql`)
Para garantizar la velocidad del sistema incluso con miles de registros de ventas y consultas, se definieron los siguientes índices estratégicos:
*   `idx_producto_proveedor` y `idx_producto_categoria`: Optimizan la velocidad de búsqueda de inventario y filtrado.
*   `idx_lote_vencimiento`: **CRÍTICO**. Permite que el algoritmo FEFO localice y ordene al instante los lotes de productos más próximos a expirar sin realizar escaneos completos de tabla.
*   `idx_kardex_fecha` e `idx_venta_fecha`: Optimizan los tiempos de respuesta al generar reportes gerenciales agrupados por periodos de tiempo (día, semana, mes).
*   `idx_cita_fecha` e `idx_consulta_expediente`: Aceleran la carga de expedientes médicos y la verificación de disponibilidad de la agenda de citas.

---

## 🔒 Autenticación y Seguridad

El sistema implementa sólidas prácticas de protección de datos basadas en los estándares OWASP y las utilidades nativas de seguridad de Laravel:

1.  **Encriptación de Credenciales**: Las contraseñas de los usuarios se almacenan procesadas con el algoritmo de derivación de claves **bcrypt** robusto (`BCRYPT_ROUNDS=12`), asegurando que no puedan revertirse.
2.  **Middlewares de Seguridad**:
    *   `VerificarRol`: Intercepta cada solicitud de ruta sensible y valida que el campo `rol` del usuario (`dueno`, `medico`, `vendedor`) cuente con los permisos requeridos.
    *   `SesionActiva`: Compara el estado del usuario en cada petición web. Si el administrador desactiva a un usuario (`estado = 'inactivo'`), este middleware invalida su sesión y lo redirige al formulario de Login inmediatamente.
3.  **Protección Contra Ataques Web Comunes**:
    *   **CSRF (Cross-Site Request Forgery)**: Todas las solicitudes de mutación de estado (`POST`, `PUT`, `DELETE`, `PATCH`) validan un token único generado por sesión para prevenir solicitudes maliciosas externas.
    *   **Inyección SQL**: El uso riguroso del ORM Eloquent y el constructor de consultas de Laravel asegura el enlace de parámetros (Parameter Binding), anulando por completo la posibilidad de inyecciones SQL.
    *   **XSS (Cross-Site Scripting)**: La directiva de renderizado de Blade (`{{ }}`) aplica automáticamente la función `htmlspecialchars` de PHP para evitar la ejecución de scripts maliciosos inyectados por los usuarios.


---

## 🐳 Entorno de Contenedores (Docker)

El sistema está completamente contenedorizado para garantizar que se ejecute de la misma manera en tu máquina local que en cualquier nube en producción.

### Servicios Definidos en `docker-compose.yml`

El entorno local consta de 4 contenedores interconectados mediante una red privada virtual tipo bridge (`farmacia_net`):

1.  **`farmacia_nginx` (Servidor Web)**:
    *   **Base**: `nginx:alpine`
    *   **Puerto**: Mapea el puerto local `8080` al puerto `80` del contenedor.
    *   **Función**: Servidor frontal. Despacha directamente imágenes, CSS y JS, y reenvía el código PHP al contenedor correspondiente.
2.  **`farmacia_php` (Capa de Aplicación Backend)**:
    *   **Base**: Compilado personalizado mediante `php/Dockerfile` (`php:8.3-fpm-bookworm`).
    *   **Extensiones**: Incluye herramientas como zip, unzip, git, curl y librerías de manipulación gráfica (GD) para DomPDF.
    *   **Función**: Procesa todo el código PHP y la lógica de negocio de Laravel.
3.  **`farmacia_mysql` (Capa de Datos)**:
    *   **Base**: `mysql:8.0`
    *   **Puerto**: Mapea el puerto local `3307` al interno `3306` para evitar colisiones con bases de datos locales.
    *   **Volumen**: `farmacia_mysql_data` para asegurar la persistencia de los datos entre reinicios del contenedor.
    *   **Salud**: Cuenta con un `healthcheck` que detiene el inicio de la app de PHP hasta que el motor de base de datos responda de manera exitosa.
4.  **`farmacia_node` (Servidor de Desarrollo Frontend)**:
    *   **Base**: `node:20-alpine`
    *   **Puerto**: Mapea el puerto `5173` para habilitar el servidor de desarrollo rápido de Vite (HMR - Hot Module Replacement).

---

## ☁️ Guía de Despliegue en Producción (AWS EC2)

El despliegue en un servidor de producción AWS EC2 (o cualquier VPS con Ubuntu Server) se gestiona de manera profesional y segura a través de `docker-compose.prod.yml` y variables de entorno externas.

### Paso 1: Configuración en el Servidor AWS EC2
1.  Instala Docker en tu servidor EC2:
    ```bash
    sudo apt update
    sudo apt install docker.io docker-compose -y
    sudo usermod -aG docker $USER
    # Cierra sesión y vuelve a entrar para aplicar cambios
    ```

2.  Clona el repositorio en el directorio `/var/www/farmacia-vida`:
    ```bash
    sudo mkdir -p /var/www/farmacia-vida
    sudo chown -R $USER:$USER /var/www/farmacia-vida
    git clone https://github.com/EduardoAdamen/Farmacia-y-Consultorio-Vida.git /var/www/farmacia-vida
    cd /var/www/farmacia-vida
    ```

### Paso 2: Configurar las Variables de Entorno de Producción
1.  Copia el archivo de configuración AWS:
    ```bash
    cp .env.aws.example .env.aws
    ```
2.  Edita `.env.aws` y ajusta los valores reales obligatorios:
    *   Define una clave segura para la base de datos MySQL (`MYSQL_ROOT_PASSWORD` y `MYSQL_PASSWORD`).
    *   Establece la URL pública del servidor (`APP_URL=http://tu-dominio-o-ip`).
    *   Genera una llave aleatoria y segura para Laravel (`APP_KEY`):
        ```bash
        # Puedes generar una llave rápida ejecutando localmente:
        echo "base64:$(openssl rand -base64 32)"
        ```
    *   Habilita la automatización del primer arranque:
        ```env
        RUN_MIGRATIONS=true
        RUN_SEEDERS=true
        CACHE_ROUTES=true
        ```

### Paso 3: Lanzar en Modo Producción
El archivo `docker-compose.prod.yml` utiliza la técnica **Multi-Stage Build** para construir una imagen de producción optimizada. Compila los recursos de Node en un contenedor temporal, copia los binarios resultantes y los empaqueta en una imagen PHP final limpia sin dependencias de desarrollo.

1.  Ejecuta el despliegue:
    ```bash
    docker-compose -f docker-compose.prod.yml --env-file .env.aws up -d --build
    ```

2.  **Verificación**:
    El contenedor de Nginx incluye una prueba de salud que valida que la aplicación responda correctamente en la ruta interna `/up`. Puedes revisar el estado escribiendo:
    ```bash
    docker-compose -f docker-compose.prod.yml ps
    ```

---

## 🗺️ Roadmap de Desarrollo

El núcleo operativo del sistema está completado al 100%. Se proponen las siguientes mejoras recomendadas para futuras fases de desarrollo:

- [ ] **Módulo de Facturación Electrónica**: Integración con PAC para la generación de CFDI de compras y ventas de medicamentos.
- [ ] **Notificaciones en Tiempo Real**: Envío automático de recordatorios de citas a pacientes vía WhatsApp o correo electrónico.
- [ ] **Respaldos Automatizados**: Scripts programados con AWS S3 para respaldar la base de datos MySQL y la carpeta de recetas generadas diariamente.
- [ ] **Módulo de Compras Sugeridas**: Generación automática de propuestas de pedidos a proveedores basada en el análisis de rotación de inventarios y stock mínimo.
- [ ] **Modo Desconectado (Offline)**: Implementación de PWA (Progressive Web App) para permitir la realización de consultas y ventas básicas sin conexión a Internet, sincronizándose al recuperar la red.

---

## 🤝 Contribución

¡Las contribuciones son lo que hacen a la comunidad de código abierto un lugar increíble para aprender, inspirar y crear! Si deseas colaborar:

1.  Haz un **Fork** del proyecto.
2.  Crea una rama para tu característica: `git checkout -b feature/NuevaFuncionalidad`
3.  Realiza tus cambios y haz commit: `git commit -m 'Añade nueva funcionalidad'`
4.  Empuja la rama: `git push origin feature/NuevaFuncionalidad`
5.  Abre un **Pull Request** detallando tus modificaciones.

---

## 📄 Licencia

Este proyecto está bajo la licencia **MIT**. Puedes consultar el archivo para más detalles.

---

## 👥 Autor y Créditos

*   **Desarrollador Principal**: [Eduardo Adame](https://github.com/EduardoAdamen)
*   **Organización / Contexto**: Proyecto de Ingeniería de Software para la gestión de la clínica y farmacia "Farmacia y Consultorio Vida".
*   **Colaboradores de Prueba**:
    *   *Carlos Mendoza* (Rol: Dueño)
    *   *Ana Ruiz* (Rol: Vendedora)
    *   *Dr. Roberto Silva* (Rol: Médico)

---

<p align="center">
  <b>Hecho con ❤️ utilizando Laravel, Tailwind CSS, MySQL, Nginx y Docker.</b>
</p>
