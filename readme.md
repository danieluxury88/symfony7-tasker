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
* **UI**: Bootstrap 5 por CDN (estilos mínimos, sin JS complejo).

> **Nota sobre usuarios**: No hay login/registro. La relación `createdBy` existe para cumplir el requerimiento y apunta a un **usuario demo** creado por fixtures.

---

## Tecnologías

* PHP **8.2+**
* Symfony **7.x** (`symfony/framework-bundle`, `twig`)
* Doctrine ORM + Migrations
* SQLite
* Symfony Validator, Form, CSRF
* Bootstrap 5 (CDN)
* PHPUnit

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
    TaskController.php
  Entity/
    Task.php
    User.php         # Entidad mínima para soportar la FK createdBy
  Form/
    TaskType.php
  Repository/
    TaskRepository.php
templates/
  base.html.twig
  task/
    index.html.twig
    new.html.twig
    edit.html.twig
tests/
  Functional/
    TaskCrudTest.php
  Unit/
    TaskValidationTest.php
```

---

## Decisiones y alcance

* **Sin autenticación**: El requerimiento no la menciona; se minimiza el alcance. Se conserva `createdBy` como FK hacia `User` para satisfacer la especificación. El `User` se crea únicamente vía fixtures.
* **SQLite**: Simplifica la evaluación y ejecución local.
* **Bootstrap por CDN**: Permite una UI adecuada sin configuración adicional de build.
* **CSRF y Validación**: Formularios protegidos y constraints esenciales en `Task`.


---

## Licencia

MIT © Daniel Proaño

---

## Autor y contacto

* **Autor**: Daniel Proaño
* **Email**: daniel.proano.88@gmail.com
* **LinkedIn**: https://www.linkedin.com/in/danielproano88/

---
