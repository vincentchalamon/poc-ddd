# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Domain-Driven Design (DDD) proof-of-concept project built with Symfony 8.0, API Platform, and Doctrine ORM. It demonstrates bounded contexts, event-driven architecture, and CQRS patterns through a simplified "dating app" domain using Sock entities.

**Tech Stack**: PHP 8.5+, Symfony 8.0, API Platform 4.2, Doctrine ORM 3.0, PostgreSQL 18

## Architecture

### Bounded Contexts

The codebase is organized into three bounded contexts under `src/`:

1. **Drawer** (`src/Drawer/`)
   - Source of truth for Sock entities
   - Exposes API endpoints for creating, reading, and updating Socks
   - Publishes domain events (e.g., `SockCreated`) to other contexts
   - Contains full API Platform integration with State Providers/Processors

2. **Laundromat** (`src/Laundromat/`)
   - Maintains its own representation of Socks with credits management
   - Listens to events from Drawer context (via Symfony Messenger)
   - Implements CQRS pattern with explicit Query handlers
   - Has read-only API endpoints for querying Socks

3. **Shared** (`src/Shared/`)
   - Common domain primitives (Identifiers, Value Objects like NonEmptyString, FloatValue)
   - Infrastructure abstractions (custom Doctrine types, API Platform metadata factories, Symfony normalizers)
   - Reusable components across all contexts

### Layered Architecture (per Context)

Each bounded context follows DDD layered architecture:

- **Domain/**: Pure domain logic
  - `Model/`: Entities, Aggregates, Value Objects
  - `Repository/`: Repository interfaces (no implementations)
  - `Exception/`: Domain-specific exceptions

- **Infrastructure/**: Technical implementations
  - `ApiPlatform/`: State Providers, Processors, API Resources
  - `Doctrine/`: Entity mappings, Repositories, Custom Types
  - `Event/`: Domain events (published via Symfony Messenger)
  - `Symfony/`: Normalizers, Serializers, DI configuration

- **Application/**: Application services (Laundromat and Shared only)
  - `Query/`: Query objects and handlers (CQRS read side)
  - `Event/`: Event handlers that react to domain events
  - `Model/`: Application-level DTOs/models

### Key Patterns

- **Event-Driven Communication**: Contexts communicate via domain events published through Symfony Messenger
- **CQRS**: Explicit separation of read (Query handlers) and write operations (especially in Laundromat)
- **API Platform State Pattern**: Custom State Providers and Processors instead of controllers
- **Value Objects**: Extensive use of type-safe value objects (EmailAddress, NonEmptyString, Identifier, etc.)
- **Custom Doctrine Types**: Value objects are stored using custom Doctrine DBAL types
- **Repository Pattern**: Domain defines interfaces, Infrastructure provides Doctrine implementations

## Development Commands

All commands use the `Makefile` for consistency. Run `make help` to see all available commands.

### Running Tests

```bash
make tests                    # Run all tests (includes linting, PHPUnit, PHPStan, security checks)
make phpunit                  # Run all PHPUnit tests
make phpunit.unit            # Run only Unit tests
make phpunit.functional      # Run only Functional/API tests
make phpunit.utils           # Run only Utils tests (PHPStan/Rector rule tests)
make phpunit.path <path>     # Run tests for specific file/directory
```

### Code Quality

```bash
make lint                    # Run all linters (CS, Rector, Markdown, Dockerfile)
make lint.fix               # Fix all auto-fixable issues

make phpstan                # Run PHPStan static analysis
make phpstan.path <path>    # Analyze specific file/directory

make cs.fix                 # Fix code style issues (PHP-CS-Fixer)
make cs.lint                # Check code style without fixing
make cs.fix.path <path>     # Fix code style for specific file/directory

make rector.fix             # Run Rector automated refactoring
make rector.lint            # Run Rector in dry-run mode
make rector.fix.path <path> # Run Rector on specific file/directory
```

### Other Tools

```bash
make security.check         # Check for known vulnerabilities (symfony check:security)
make openapi.lint          # Validate OpenAPI schema
make markdown.lint         # Check Markdown files
make dockerfile.lint       # Lint all Dockerfiles with hadolint
make doc                   # Run documentation locally (PHPMetrics + MkDocs on localhost:8000)
```

### Docker Environment

```bash
docker compose up -d        # Start PHP and PostgreSQL services
docker compose down         # Stop services
docker compose logs -f php  # View PHP container logs
```

## Working with the Codebase

### Adding a New Entity to a Context

1. Create the domain model in `{Context}/Domain/Model/`
2. Define repository interface in `{Context}/Domain/Repository/`
3. Implement repository in `{Context}/Infrastructure/Doctrine/Repository/`
4. Add API Platform resource attributes to the model or create separate API Resource in `{Context}/Infrastructure/ApiPlatform/ApiResource/`
5. Create State Provider/Processor in `{Context}/Infrastructure/ApiPlatform/State/`
6. Add Doctrine mappings (via attributes or XML)
7. Generate and run migrations: `bin/console doctrine:migrations:diff && bin/console doctrine:migrations:migrate`

### Creating Value Objects

1. Define interface/class in `Shared/Domain/{Category}/` (e.g., Text, Number, Identifier)
2. Create custom Doctrine DBAL type in `Shared/Infrastructure/Doctrine/DBAL/Types/{Category}/`
3. Create Symfony normalizer in `Shared/Infrastructure/Symfony/Serializer/{Category}/`
4. Register the type in `Shared/Infrastructure/Symfony/DependencyInjection/config.php`
5. Optionally create API Platform metadata factory for proper OpenAPI documentation

### Inter-Context Communication

To communicate between contexts:

1. Create domain event in source context: `{SourceContext}/Infrastructure/Event/`
2. Dispatch event using Symfony Messenger in State Processor or service
3. Create event handler in target context: `{TargetContext}/Application/Event/{EventName}Handler.php`
4. Tag handler with `#[AsMessageHandler]` attribute or configure in `services.php`

### Running Single Tests

```bash
# Run specific test file
make phpunit.path tests/Api/Drawer/SockTest.php

# Run specific test method
vendor/bin/phpunit --filter testMethodName tests/Api/Drawer/SockTest.php
```

## Code Standards

- **PHP Version**: Minimum PHP 8.5
- **PHPStan Level**: Level 6 with bleeding edge rules enabled
- **Type Safety**: Strict types required (`declare(strict_types=1);` in all files)
- **Banned Functions**: No usage of `dd()`, `dump()`, `var_dump()`, `print_r()`, `echo`, `die()`, `exit()`, `eval()`, `exec()`, `shell_exec()`, `system()`, `proc_open()`, `passthru()`, `phpinfo()`, `debug_backtrace()`
- **Domain Purity**: Domain layer must not depend on Infrastructure or Application layers
- **Repository Pattern**: Domain defines interfaces, Infrastructure implements them using Doctrine
- **No Anemic Models**: Domain models should contain business logic, not just getters/setters
- **Immutability Preferred**: Use readonly properties where possible; value objects should be immutable

## Testing Conventions

- **Functional Tests**: Use API Platform's test client for endpoint testing in `tests/Api/`
- **Unit Tests**: Test domain logic in isolation (no database, no HTTP)
- **Architecture Tests**: Verify architectural constraints using PHPat in `tests/Architecture/`
- **Fixtures**: Use Zenstruck Foundry for test data generation
- **Database**: Tests use the test environment (`APP_ENV=test`) with separate database

## Important Files

- `config/services.php`: Service container configuration
- `config/packages/*.yaml`: Bundle-specific configuration
- `phpstan.dist.neon`: PHPStan configuration with custom rules
- `rector.php`: Rector automated refactoring rules
- `.php-cs-fixer.dist.php`: PHP-CS-Fixer code style rules
- `phpunit.xml.dist`: PHPUnit configuration with test suites
- `compose.yaml`: Docker Compose services (PHP 8.5, PostgreSQL 18)
- `mkdocs.yml`: Documentation configuration
