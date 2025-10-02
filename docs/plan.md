# Objetivo

Entregar una aplicación **Symfony 7** con un **CRUD de Tareas (Task)**, autenticación de usuarios, buenas prácticas de seguridad/validación, README de instalación/uso y **video demostrativo**. Se optimiza para completar en 48h con foco en calidad y claridad.

---

# Alcance funcional (mínimo requerido)

* **CRUD de Task**: listar, crear, editar, eliminar.
* **Entidad Task** con: `id`, `title` (obligatorio), `description?`, `isCompleted=false`, `createdAt` (auto), `createdBy` (**FK** a `User` **pero sin sistema de autenticación**; se asigna al **usuario demo** creado por fixtures).
* Validaciones básicas (ej.: `title` no vacío, longitudes razonables).
* Extras opcionales (si hay tiempo, no obligatorios): filtros por estado, búsqueda en título, paginación, toggle de completado con HTMX.

---

# Stack técnico propuesto

* **PHP 8.2+**, **Symfony 7.1** (o 7.x).
* **Doctrine ORM** + **SQLite** (simple para correr en cualquier entorno).
* **Twig** para vistas.
* **Bootstrap 5** (CDN) para UI rápida.
* **HTMX** (CDN) para interacciones sin recargar (solo si hay tiempo).
* **MakerBundle**, **Doctrine Migrations**, **Fixtures**.
* **PHPUnit** para 2–3 tests básicos.

---

# Estructura de trabajo por fases (48h)

**Fase 0 – Repo y baseline **

1. Crear repo GitHub público `symfony7-tasker` (MIT).
2. Crear proyecto: `symfony new tasker --version=7.1 --webapp` (o Composer).
3. Commit inicial.

**Fase 1 – Modelo y base de datos**

1. `composer require orm maker annotations` (si no están).
2. **Entidad User mínima (sin auth):** `id`, `email` (único) y opcional `name`. *No* se implementa seguridad/login.
3. **Entidad Task** según requisitos: `title`, `description?`, `isCompleted=false`, `createdAt`, `createdBy: ManyToOne(User)`.
4. Migraciones: `bin/console make:migration && bin/console doctrine:migrations:migrate`.

**Fase 2 – CRUD**

1. `bin/console make:crud Task` (controller, form, vistas base).
2. Ajustar **TaskController**:

   * En `new()`: setear `createdAt` automático (constructor) y `createdBy` con el **usuario demo** obtenido desde repositorio (por ejemplo el primero o por email configurable por env).
   * En `index()`: listado general (sin filtrado por usuario).

**Fase 3 – UI/UX**

1. Layout `base.html.twig` con navbar simple (Tasks, About, etc.).
2. Lista con botones crear/editar/eliminar y mensajes flash.
3. **Opcional:** toggle de completado con HTMX.

**Fase 4 – Fixtures y pruebas**

1. `composer require --dev orm-fixtures` y `bin/console make:fixtures`.
2. Crear **usuario demo** (ej.: `demo@example.com`) y 10–20 **tareas** asociadas.
3. **Tests**:

   * Validación de `title` requerido.
   * Flujo CRUD básico con `HttpKernelBrowser` (crear → editar → completar → eliminar).
   * Verificar que al crear una tarea se asigna automáticamente `createdBy` al usuario demo.

**Fase 5 – README y video**

1. README con: requisitos, instalación, comandos, usuarios demo, capturas y enlace al video.
2. Grabar video (2–3 min) mostrando el CRUD funcionando.

**Fase 6 – Pulido**

* Revisión de validaciones, CSRF en formularios, manejo de errores 404/500, limpieza de código.

---

# Comandos y snippets (guía rápida)



## Configurar SQLite (doctrine.yaml)

```yaml
# config/packages/doctrine.yaml
doctrine:
  dbal:
    url: '%env(resolve:DATABASE_URL)%'
  orm:
    auto_generate_proxy_classes: true
    enable_lazy_ghost_objects: true
    mappings:
      App:
        is_bundle: false
        type: attribute
        dir: '%kernel.project_dir%/src/Entity'
        prefix: 'App\\Entity'
        alias: App
```

```
# .env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

## Usuario demo (sin login)

> No se implementa registro ni autenticación. Se crea una **entidad User mínima** para soportar la FK `createdBy` y se pobla mediante **fixtures**. La UI no expone usuarios.

````bash
bin/console make:entity User
# Campos sugeridos: email (string unique), name (string opcional)
```bash
bin/console make:user User
# email (string unique), password (hashed), roles (array)
bin/console make:auth
# Elegir: Login form authenticator
````

## Entidad Task

```bash
bin/console make:entity Task
# title: string(180) not null
# description: text nullable
# isCompleted: boolean default false
# createdAt: datetime_immutable not null
# createdBy: relation ManyToOne -> User (inversedBy: tasks), not null

bin/console make:migration
bin/console doctrine:migrations:migrate
```

**Constructor y validaciones (resumen):**

```php
// src/Entity/Task.php
#[ORM\Entity]
class Task
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    #[ORM\Column(length: 180)]
    private string $title = '';

    #[Assert\Length(max: 5000)]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isCompleted = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
```

## CRUD y ajustes de controlador

```bash
bin/console make:crud Task
```

Ajustes clave en `TaskController`:

* En `new()`, tras `handleRequest`, antes de persistir: `\$task->setCreatedBy(\$this->getUser());`
* En `index()`, usar un método repo como `findForUser(User $u, ?string $q, ?string $state, int $page)`.

**Ejemplo de método de repositorio:**

```php
public function findForUser(User $user, ?string $q, ?string $state, int $page = 1): Pagerfanta
{
    $qb = $this->createQueryBuilder('t')
        ->andWhere('t.createdBy = :u')->setParameter('u', $user)
        ->orderBy('t.createdAt', 'DESC');

    if ($q) {
        $qb->andWhere('LOWER(t.title) LIKE :q')
           ->setParameter('q', '%'.mb_strtolower($q).'%');
    }
    if ($state === 'done') $qb->andWhere('t.isCompleted = true');
    if ($state === 'todo') $qb->andWhere('t.isCompleted = false');

    return PagerfantaAdapter::paginate($qb, $page, 10);
}
```

(Usar `pagerfanta/doctrine-orm-adapter` si deseas paginar.)

## (Sin seguridad/autenticación)

No se implementan voters ni `security.yaml`. El campo `createdBy` existe para cumplir el requisito de la FK y se completa con el **usuario demo** definido en fixtures.bash
bin/console make:voter TaskVoter

````
Regla: permitir `VIEW/EDIT/DELETE` solo si `task.createdBy === user` o si `user` tiene `ROLE_ADMIN`.

## HTMX: toggle de completado
- Botón en la fila con:
```twig
<form hx-post="{{ path('task_toggle', {id: task.id}) }}" hx-swap="outerHTML">
  <input type="hidden" name="_token" value="{{ csrf_token('toggle' ~ task.id) }}">
  <button class="btn btn-sm" title="Toggle">{{ task.isCompleted ? '✅' : '⬜' }}</button>
</form>
````

* Acción en el controlador `toggle(Task $task, Request $r)` que valida CSRF, invierte `isCompleted`, guarda y devuelve el **fragmento** renderizado de la fila.

## Fixtures

```bash
composer require --dev orm-fixtures fakerphp/faker
bin/console make:fixtures AppFixtures
```

Crear usuarios demo: `user@example.com` (ROLE_USER) / `admin@example.com` (ROLE_ADMIN) con password `test1234` y 15–20 tareas aleatorias.

## Tests mínimos

* **PolicyTest**: usuario A no edita/elimina tarea de B → 403.
* **TaskFlowTest**: crear/editar/toggle/eliminar con `HttpKernelBrowser`.

---

# UI rápida (Bootstrap + HTMX por CDN)

**`base.html.twig` – includes**

```twig
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/htmx.org@2.0.3" defer></script>
```

Navbar con enlaces a **Tasks**, **Login/Logout**, y alerta de sesión.

---

# README.md (esqueleto sugerido)

1. **Proyecto y demo** (breve explicación + captura + link al video).
2. **Requisitos** (PHP 8.2+, Composer, Symfony CLI opcional).
3. **Instalación**

   ```bash
   git clone https://github.com/<user>/symfony7-tasker
   cd symfony7-tasker
   composer install
   bin/console doctrine:database:create # opcional en sqlite
   bin/console doctrine:migrations:migrate
   bin/console doctrine:fixtures:load -n
   symfony serve -d # o php -S 127.0.0.1:8000 -t public
   ```
4. **Usuarios demo**

   * `user@example.com` / `test1234`
   * `admin@example.com` / `test1234`
5. **Características** (CRUD, filtros, paginación, toggle, seguridad).
6. **Estructura del código** y decisiones (por qué SQLite, HTMX, Voter, etc.).
7. **Pruebas**: `composer test` (incluir `phpunit.xml.dist`).
8. **Seguridad** (CSRF, acceso por propietario, validaciones).
9. **Licencia** (MIT).

---

# Guion del video (2–3 min)

1. Inicio: pantallas de Login → ingreso con usuario demo.
2. Lista de tareas: filtros/paginación; toggle sin recargar (HTMX).
3. Crear tarea (validación de título en blanco).
4. Editar → guardar.
5. Intento de acceder a URL de edición de otra tarea (mostrar 403) **o** explicar Voter.
6. Eliminar con confirmación.
7. Cierre: repo público + cómo correr en local.

---

# Definición de Hecho (DoD)

* ✅ CRUD completo con validaciones y seguridad.
* ✅ `createdBy` seteado automáticamente y respetado en consultas.
* ✅ Voter aplicado en editar/eliminar.
* ✅ README con pasos reproducibles y usuarios demo.
* ✅ Video enlazado en README.
* ✅ 1–3 tests que pasen localmente.

---

