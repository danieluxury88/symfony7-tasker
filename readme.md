# Tasker — Symfony 7 CRUD de Tareas

Mini-proyecto de evaluación técnica: una aplicación **Symfony 7** con un **CRUD** para gestionar una lista de tareas (**Task**). Cumple estrictamente los requisitos solicitados: entidad `Task`, operaciones de **crear / listar / editar / eliminar**, uso de **Doctrine ORM**, base de datos **SQLite**, **sin sistema de autenticación** (pero manteniendo `createdBy` como **FK** a un `User` mínimo creado por fixtures), validaciones básicas y pruebas automáticas.

---

## Características

* **Entidad `Task`**

  * `id` (int, autogenerado)
  * `title` (string, requerido)
  * `description` (text, opcional)
  * `isCompleted` (bool, por defecto `false`)
  * `createdAt` (datetime_immutable, por defecto fecha/hora de creación)
  * `createdBy` (FK a `User`) — *se asigna automáticamente al usuario demo cargado por fixtures*
* **CRUD completo** (Twig): listar, crear, editar, eliminar.
* **Validaciones** (Symfony Validator): `title` requerido + longitudes razonables.
* **Base de datos**: SQLite (portabilidad y facilidad de instalación).
* **Fixtures**: crean un usuario demo y varias tareas de ejemplo.
* **Pruebas automáticas** (PHPUnit): validación de `title` + flujo CRUD básico.
* **UI**: Bootstrap 5 integrado vía Asset Mapper (diseño responsive, componentes modernos).
* **Tabla ordenable**: Funcionalidad de sorting por encabezados (ID, Título, Descripción, Estado, Fecha).

> **Nota sobre usuarios**: No hay login/registro. La relación `createdBy` existe para cumplir el requerimiento y apunta a un **usuario demo** creado por fixtures.

---

## Tecnologías y Bundles

### Core Symfony
* PHP **8.2+**
* Symfony **7.3.x** (`symfony/framework-bundle`, `symfony/twig-bundle`)
* Doctrine ORM + Migrations Bundle
* SQLite
* Symfony Validator, Form, CSRF

### Bundles Adicionales
* **symfony/maker-bundle** - Generación de código (entidades, controladores, formularios)
* **symfony/asset-mapper** - Gestión moderna de assets sin Node.js
* **doctrine/doctrine-fixtures-bundle** - Carga de datos de prueba
* **symfony/stimulus-bundle** - Integración con Stimulus (Hotwired)
* **symfony/ux-turbo** - Navegación SPA-like sin JavaScript complejo
* **symfony/web-profiler-bundle** - Debugging y profiling (solo desarrollo)

### Frontend
* **Bootstrap 5.3.x** - Framework CSS moderno integrado vía Asset Mapper
* **Font Awesome** - Iconografía
* **Stimulus** - JavaScript ligero y estructurado

### Testing
* **PHPUnit 12.x** - Testing framework
* **symfony/browser-kit** - Testing de aplicaciones web

---

## Requisitos

* **PHP 8.2+** con extensiones típicas de Symfony (intl, pdo_sqlite, etc.)
* **Composer**
* **Symfony CLI** *(opcional, recomendado)*

---

## Instalación

### 🚀 Método Recomendado: Script de Instalación Automática

El proyecto incluye un script de instalación que configura todo automáticamente:

```bash
# 1) Clonar el repositorio
git clone https://github.com/danieluxury88/symfony7-tasker.git
cd symfony7-tasker

# 2) Ejecutar el script de instalación
./install.sh
```

El script automáticamente:
- ✅ Verifica los requisitos del sistema (PHP 8.2+, Composer)
- ✅ Instala todas las dependencias de Composer
- ✅ Configura el entorno de desarrollo (.env.local)
- ✅ Crea y migra la base de datos SQLite
- ✅ Carga fixtures con datos de ejemplo
- ✅ Configura assets y cache
- ✅ Ejecuta tests para verificar la instalación
- ✅ Proporciona instrucciones de inicio

### 🛠️ Instalación Manual (Alternativa)

Si prefieres instalar manualmente:

```bash
# 1) Clonar el repositorio
git clone https://github.com/danieluxury88/symfony7-tasker.git
cd symfony7-tasker

# 2) Instalar dependencias
composer install

# 3) Configurar entorno
cp .env .env.local
# Editar .env.local si es necesario

# 4) Preparar la base de datos
bin/console doctrine:database:create --if-not-exists
bin/console doctrine:migrations:migrate -n
bin/console doctrine:fixtures:load -n

# 5) Configurar assets
bin/console importmap:install
bin/console asset-map:compile
```

### Configuración de Base de Datos

La app está configurada para usar **SQLite** por defecto:

```
# .env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_%kernel.environment%.db"
```

> Si deseas sobreescribir el DSN, crea un `.env.local` con tu `DATABASE_URL`.

---

## Ejecución

### Después de usar el script de instalación

El script te mostrará las instrucciones exactas al final, pero típicamente:

```bash
# Iniciar servidor de desarrollo
symfony server:start
# O alternativamente: php -S localhost:8000 -t public/

# Abrir en el navegador
# Con Symfony CLI: https://127.0.0.1:8000
# Con PHP nativo: http://localhost:8000
```

### Inicio rápido manual

Con Symfony CLI (recomendado):

```bash
symfony serve -d
# luego abre http://127.0.0.1:8000
```

O nativo en PHP:

```bash
php -S 127.0.0.1:8000 -t public
# abre http://127.0.0.1:8000
```

### 🎯 Funcionalidades Disponibles

- **Gestión completa de tareas**: Crear, editar, ver y eliminar
- **Tabla ordenable**: Haz clic en cualquier encabezado (ID, Título, Descripción, Estado, Fecha) para ordenar
- **Interfaz responsive**: Diseño Bootstrap optimizado para móvil y desktop
- **Datos de ejemplo**: El script carga automáticamente tareas de prueba

---

## Uso

* **Lista de tareas**: `GET /tasks`
* **Crear**: `GET /tasks/new` → enviar formulario `POST`
* **Editar**: `GET /tasks/{id}/edit` → `POST`
* **Eliminar**: `POST /tasks/{id}` con token CSRF

> El campo `createdBy` se asigna automáticamente en el controlador al **usuario demo** creado por fixtures (p.ej. `demo@example.com`). No hay interfaz para cambiar de usuario ni autenticación.

---

## Pruebas

```bash
# Ejecutar tests
./vendor/bin/phpunit
# o si usas symfony/phpunit-bridge
# bin/phpunit
```

Los tests incluidos cubren:

* **Validación**: `title` requerido.
* **Flujo CRUD**: crear → editar → (opcional) marcar completado → eliminar, verificando respuestas/redirects.

---

## Estructura (resumen)

```
src/
  Controller/
    TaskController.php       # CRUD con sorting
  Entity/
    Task.php                # Entidad principal
    User.php                # Entidad mínima para soportar FK createdBy
  Form/
    TaskType.php            # Formulario Symfony
  Repository/
    TaskRepository.php      # Consultas personalizadas con sorting
  DataFixtures/
    AppFixtures.php         # Datos de prueba
assets/
  app.js                    # Stimulus controllers
  bootstrap.js              # Bootstrap y dependencias
  styles/
    app.css                 # Estilos personalizados
  controllers/              # Stimulus controllers
templates/
  base.html.twig            # Layout base con Asset Mapper
  task/
    index.html.twig         # Lista con tabla ordenable
    new.html.twig           # Formulario de creación
    edit.html.twig          # Formulario de edición
    show.html.twig          # Vista detallada
tests/
  Controller/
    TaskControllerTest.php  # Tests funcionales
  FixturesLoadedTest.php    # Tests de fixtures
config/
  packages/
    asset_mapper.yaml       # Configuración Asset Mapper
    doctrine_migrations.yaml
    doctrine.yaml
install.sh                  # Script de instalación automática
```

---

## Decisiones y alcance

* **Sin autenticación**: El requerimiento no la menciona; se minimiza el alcance. Se conserva `createdBy` como FK hacia `User` para satisfacer la especificación. El `User` se crea únicamente vía fixtures.
* **SQLite**: Simplifica la evaluación y ejecución local sin configuración adicional de base de datos.
* **Asset Mapper**: Moderna gestión de assets sin Node.js, integrando Bootstrap 5 y Stimulus de forma nativa.
* **Maker Bundle**: Facilita la generación de código y prototipado rápido.
* **Doctrine Fixtures**: Permite cargar datos de prueba consistentes para desarrollo y testing.
* **Tabla ordenable**: Implementación server-side para mejor rendimiento con grandes datasets.
* **CSRF y Validación**: Formularios protegidos y constraints esenciales en `Task`.
* **UX Bundles**: Stimulus y Turbo para interactividad moderna sin JavaScript complejo.


---

## Licencia

MIT © Daniel Proaño

---

## Autor y contacto

* **Autor**: Daniel Proaño
* **Email**: daniel.proano.88@gmail.com
* **LinkedIn**: https://www.linkedin.com/in/danielproano88/

---
