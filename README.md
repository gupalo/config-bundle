Config Bundle
=============

Install
-------

Composer

    composer req gupalo/config-bundle

Check if bundle was added to `config/bundles.php`

    Gupalo\ConfigBundle\ConfigBundle::class => ['all' => true],

Add routes - create `config/routes/config.yaml`

    config:
        resource: "@ConfigBundle/Resources/config/routing/routing.yaml"

Add translations - insert to `translations/messages.en.yaml`

    btn:
        create: Create
        delete: Delete
        save: Save

    col:
        id: ID
        name: Name
        value: Value

    config:
        heading: Config
        new:
            heading: New Config
        edit:
            heading: Edit Config

Create `config` table in DB

    php bin/console make:migration
    php bin/console doctrine:migrations:migrate -n

Add to `service.yaml` possible values and default values

    parameters:
        ...
        config.defaults:
            SOME_PARAM: 0
            OTHER_PARAM: '-2 days'

Usage
-----

Go to `/config`
