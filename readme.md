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

```bash
# 1) Clonar el repositorio
git clone https://github.com/danieluxury88/symfony7-tasker.git
cd symfony7-tasker

# 2) Instalar dependencias
composer install
```

La app está configurada para usar **SQLite** por defecto:

```
# .env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

> Si deseas sobreescribir el DSN, crea un `.env.local` con tu `DATABASE_URL`.

### Preparar la base de datos

```bash
# Crear/migrar el esquema
bin/console doctrine:database:create --if-not-exists
bin/console doctrine:migrations:migrate -n

# (Opcional) limpiar tabla de migraciones si migraste más de una vez en local
# bin/console doctrine:schema:drop --full-database --force
# bin/console doctrine:migrations:migrate -n

# Cargar fixtures (crea usuario demo y varias tareas)
bin/console doctrine:fixtures:load -n
```

---

## Ejecución

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
