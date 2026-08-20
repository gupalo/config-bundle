Config Bundle
=============

A two-column config table (`name` unique, `value` text) wired to an EasyAdmin CRUD.
Drop the bundle in, register `MenuItem::linkTo(ConfigCrudController::class, 'Config', …)`,
and your `IMAGE_*` / `FEATURE_*` flags become admin-editable without a redeploy.

Requirements
------------

- PHP 8.4+
- Symfony 7.4 or 8.0+
- Doctrine ORM 3.x
- EasyAdmin 5

Install
-------

Composer

    composer req gupalo/config-bundle

The bundle is auto-registered; no `bundles.php` edit needed.

Create the `config` table:

    php bin/console make:migration
    php bin/console doctrine:migrations:migrate -n

Declare possible values and their defaults in `config/services.yaml`. The type of each
default decides how `ConfigRepository::getValue()` decodes the stored string — a fresh
row is seeded with the default on first read, so a brand-new deploy that visits the CRUD
sees the same numbers the env vars would have produced.

    parameters:
        ...
        config.defaults:
            SOME_INT: 0
            SOME_BOOL: true
            SOME_DATETIME: '-2 days'

Wire the menu entry in your `EasyAdminMenu::configureMenuItems()`:

    use Gupalo\ConfigBundle\Controller\ConfigCrudController;

    yield MenuItem::linkTo(ConfigCrudController::class, 'Config', 'fas fa-gears');

EasyAdmin picks up the controller automatically — no routing file to add.

Reading values from your services
---------------------------------

`ConfigRepository` is autowired and public. Use the typed accessors (the type comes from
the default you declared above, so an `int` default gives you `getIntValue()`, a `bool`
default gives you `getBoolValue()`, etc.):

    public function __construct(
        private readonly ConfigRepository $configRepository,
        #[Autowire('%env(int:IMAGE_REFILL_PER_HOUR)%')]
        private readonly int $perHourFallback = 20,
    ) {}

    public function perHour(): int
    {
        // Config-bundle row wins; env var is the fallback for the row-not-present case.
        return max(1, $this->configRepository->getIntValue('IMAGE_REFILL_PER_HOUR', $this->perHourFallback));
    }

`getStringValueCached()` is the cached variant (60s TTL via `FilesystemAdapter`) — use it
from the daemon's tick loop; the un-cached accessors hit the DB on every call.

Upgrading from 1.x
------------------

1.x shipped a custom form-based UI at `/config`. 2.0 removes it — the only UI is the
EasyAdmin CRUD.

- Delete `config/routes/config.yaml` if you added it for 1.x; the bundle no longer ships
  routing.
- In `EasyAdminMenu`, replace any `MenuItem::linkToUrl('Config', …, '/config')` with
  `MenuItem::linkTo(Gupalo\ConfigBundle\Controller\ConfigCrudController::class, 'Config', …)`.
- The `Config` entity and `ConfigRepository` API (`getValue`, `getIntValue`,
  `getBoolValue`, `getStringValueCached`, …) are unchanged.
