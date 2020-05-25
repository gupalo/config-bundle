Config Bundle
=============

Install
-------

Composer

    composer req gupalo/config-bundle

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
