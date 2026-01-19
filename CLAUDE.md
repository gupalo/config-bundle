# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Symfony bundle (`gupalo/config-bundle`) that provides a simple key-value configuration storage system backed by a database table. It includes a web UI for managing config entries at `/config`.

## Common Commands

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run a single test
./vendor/bin/phpunit tests/Entity/ConfigTest.php

# Run static analysis
composer analyze
```

## Architecture

### Core Components

- **Entity** (`src/Entity/Config.php`): Simple name-value pair stored in `config` table
- **Repository** (`src/Repository/ConfigRepository.php`): Main service for reading/writing config values. Supports typed access (string, int, float, bool, array, DateTime) with automatic type inference based on defaults. Includes filesystem caching (60s TTL).
- **Controller** (`src/Controller/ConfigController.php`): Web UI for CRUD operations on config entries

### Service Configuration

The repository receives `%config.defaults%` parameter which:
1. Provides default values for config keys
2. Determines the type of values (int, float, bool, array, DateTime) based on the default's type
3. Can optionally specify custom `table_name`

Host applications configure defaults in their `services.yaml`:
```yaml
parameters:
    config.defaults:
        SOME_PARAM: 0           # int
        OTHER_PARAM: '-2 days'  # string (DateTime when parsed)
```

### Routes

Routes are defined in `src/Resources/config/routing/routing.yaml` and exposed at `/config`.

## Requirements

- PHP 8.4+
- Symfony 7.4 or 8.0+
- Doctrine ORM 3.x
