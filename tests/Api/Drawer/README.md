# Drawer Sock API Functional Tests

## Overview

Comprehensive functional tests for the Drawer bounded context's Sock API resource, covering all CRUD operations, authentication, authorization, and validation scenarios. All tests include JSON schema validation using `justinrainbow/json-schema` to ensure response structure consistency.

## Test Structure

### Factories

- **SockFactory** (`tests/Factory/Drawer/SockFactory.php`): Creates Sock entities with realistic test data using Zenstruck Foundry
- **StyleFactory** (`tests/Factory/Drawer/StyleFactory.php`): Creates Style value objects with valid size, description, keywords, and location

### JSON Schemas

Located in `tests/Api/Drawer/schemas/`:

- **openapi-sock.json**: Validates OpenAPI specification structure focusing on Sock-related paths (`/socks`, `/socks/{identifier}`) and components
- **Drawer/Sock/item.json**: Validates Sock item responses (GET, PATCH)
- **Drawer/Sock/created.json**: Validates Sock creation responses (POST)
- **Drawer/Sock/validation-error.json**: Validates API Platform validation error responses

All schemas follow JSON Schema Draft 7 specification and are validated using `justinrainbow/json-schema` via API Platform's `assertMatchesJsonSchema()` method.

### Authentication

Tests use Symfony's `loginUser()` method which directly authenticates a UserInterface object (Sock entity) without requiring actual JWT tokens. This is the recommended approach for functional testing with API Platform.

### Data Providers

Tests use PHPUnit's `#[DataProvider]` attribute to group similar test scenarios, reducing code duplication:

- **invalidSockCreationDataProvider**: Tests various invalid sock creation payloads
- **invalidStyleUpdateDataProvider**: Tests various invalid style update payloads (size constraints, location constraints, keyword requirements)
- **unauthorizedAccessDataProvider**: Tests authentication and authorization scenarios (401 Unauthorized, 403 Forbidden)

## Test Coverage

### 1. OpenAPI Schema Validation (`itValidatesOpenAPISchema`)
- **Purpose**: Ensures the OpenAPI documentation is correctly generated for Sock resources
- **Validates**:
  - API documentation endpoint `/docs.json` returns valid JSON
  - Sock-related paths (`/socks`, `/socks/{identifier}`) are present
  - All operations (POST, GET, PATCH) are documented
  - Components contain Sock schemas
- **Schema**: `tests/Api/Drawer/schemas/openapi-sock.json`

### 2. Sock Creation

#### `itCreatesASock`
- **Method**: POST /socks
- **Validates**:
  - Successful sock creation with valid email
  - Returns 201 Created status
  - Response includes identifier (UUID v7), emailAddress, name
  - Style is initially null
- **Schema**: `tests/Api/Drawer/schemas/created.json`

#### `itFailsToCreateASockWithInvalidData` (Data Provider)
- **Validates**: Various creation validation failures
- **Data Provider**: `invalidSockCreationDataProvider`
- **Scenarios**:
  - Invalid email format → 422 Unprocessable Entity
  - Missing email address → 422 with specific violation
- **Schema**: `tests/Api/Drawer/schemas/validation-error.json`

### 3. Sock Retrieval

#### `itDeniesUnauthorizedAccess` (Data Provider)
- **Validates**: Authentication and authorization across operations
- **Data Provider**: `unauthorizedAccessDataProvider`
- **Scenarios**:
  - GET another sock without authentication → 401 Unauthorized
  - GET another sock with authentication → 403 Forbidden
  - PATCH another sock with authentication → 403 Forbidden
  - PATCH own sock without authentication → 401 Unauthorized
- **Security Rule**: `user.identifier() == object.identifier()`

#### `itGetsItsSock`
- **Method**: GET /socks/{identifier}
- **Authentication**: Logged in as sock owner
- **Validates**: Successful retrieval of owned sock with all fields
- **Schema**: `tests/Api/Drawer/schemas/item.json`

#### `itGetsItsSockWithStyle`
- **Validates**: Sock retrieval when style is present
- **Checks**: Style object contains size, description, keywords, location
- **Schema**: `tests/Api/Drawer/schemas/item.json`

#### `itCannotGetNonExistentSock`
- **Validates**: 404 handling for non-existent resources
- **Expected**: 404 Not Found

### 4. Sock Updates

#### `itUpdatesItsSock`
- **Method**: PATCH /socks/{identifier}
- **Content-Type**: application/merge-patch+json
- **Validates**: Successful style update with all required fields
- **Checks**: Size, description, keywords, and location are correctly updated
- **Schema**: `tests/Api/Drawer/schemas/item.json`

#### `itFailsToUpdateSockWithInvalidStyle` (Data Provider)
- **Validates**: Various style validation constraints
- **Data Provider**: `invalidStyleUpdateDataProvider`
- **Scenarios**:
  - Size exceeds maximum (250cm) → 422 Unprocessable Entity
  - Size below minimum (100cm) → 422 Unprocessable Entity
  - Latitude exceeds maximum (90) → 422 Unprocessable Entity
  - Longitude below minimum (-180) → 422 Unprocessable Entity
  - Empty keywords array → 422 with specific violation
  - Incomplete style (only size) → 422 Unprocessable Entity
  - Missing size → 422 Unprocessable Entity
- **Schema**: `tests/Api/Drawer/schemas/validation-error.json`

#### `itUpdatesStyleMultipleTimes`
- **Validates**: Style can be updated multiple times
- **Scenario**: Two consecutive PATCH requests
- **Checks**: Second update overwrites first update
- **Schema**: `tests/Api/Drawer/schemas/item.json` (validates final response)


## API Resource Configuration

### Operations

1. **POST /socks**
   - Denormalization group: `sock:create`
   - Validation group: `sock:create`
   - Required: emailAddress
   - Processor: CreateSockProcessor

2. **GET /socks/{identifier}**
   - Security: `is_granted("ROLE_USER") and user.identifier() == object.identifier()`
   - Provider: GetSockProvider

3. **PATCH /socks/{identifier}**
   - Denormalization group: `sock:update`
   - Validation group: `sock:update`
   - Security: `is_granted("ROLE_USER") and user.identifier() == object.identifier()`
   - Provider: GetSockProvider
   - Processor: UpdateSockProcessor

### Normalization Group

- `sock:read`: identifier, emailAddress, name, style

## Domain Model Constraints

### EmailAddress
- Must be valid email format
- Validated by PHP's FILTER_VALIDATE_EMAIL

### Size
- Type: FloatValue
- Range: 100cm - 250cm
- Serialized as float

### Style
- Size: Required Size object
- Description: Required NonEmptyString
- Keywords: Array of NonEmptyString (minimum 1)
- Location: Required Location object

### Location
- Latitude: 0 to 90
- Longitude: -180 to 180
- Serialized as object: `{"latitude": float, "longitude": float}`

## Value Object Serialization

- **Identifier (UuidIdentifier)**: Serialized as UUID string
- **EmailAddress (extends NonEmptyString)**: Serialized as string
- **Name (NonEmptyString)**: Serialized as string
- **Size (extends FloatValue)**: Serialized as float
- **Description (NonEmptyString)**: Serialized as string
- **Keywords (array of NonEmptyString)**: Serialized as array of strings
- **Location**: Serialized as object with latitude/longitude properties

## Theoretical Validity Analysis

### ✅ Authentication Mechanism
- Uses Symfony Security with Sock as UserInterface
- `loginUser()` method correctly simulates authenticated requests
- No JWT bundle required for testing

### ✅ Factory Usage
- Zenstruck Foundry properly instantiates entities with value objects
- `_real()` method unwraps proxy to get actual Sock instance for authentication
- Random data generation ensures unique email addresses and names

### ✅ JSON Schema Validation
- Uses `justinrainbow/json-schema` library via API Platform's `assertMatchesJsonSchema()`
- Schemas follow JSON Schema Draft 7 specification
- Comprehensive validation of:
  - Required fields and their presence
  - Field types and formats (uuid, email, number ranges)
  - Nested object structures (Style, Location)
  - Validation error response format (Hydra ConstraintViolationList)
- Separate schemas for different response types (item, created, validation-error)
- OpenAPI schema specifically validates Sock-related paths and operations

### ✅ API Platform Assertions
- `assertResponseIsSuccessful()`: Validates 2xx status codes
- `assertResponseStatusCodeSame()`: Validates specific HTTP status codes
- `assertJsonContains()`: Partial JSON matching (subset validation)
- `assertResponseHeaderSame()`: Validates response headers
- `assertMatchesJsonSchema()`: Comprehensive schema validation

### ✅ Security Configuration
- Provider configured to load Sock entities by emailAddress
- Security expressions correctly reference user and object identifiers
- Stateless firewall appropriate for API

### ✅ Validation Groups
- `sock:create` group validates emailAddress NotNull constraint
- `sock:update` group validates style NotNull and nested constraints
- Validation groups ensure different requirements for create vs update

### ✅ Content Negotiation
- POST: `application/json`
- PATCH: `application/merge-patch+json` (RFC 7396)
- Response: `application/json; charset=utf-8`

## Running Tests

```bash
# Run all functional tests
make phpunit.functional

# Run only Sock tests
make phpunit.path tests/Api/Drawer/SockTest.php

# Run specific test method
vendor/bin/phpunit --filter itUpdatesItsSock tests/Api/Drawer/SockTest.php
```

## Architectural Considerations

### Auto-generation of Required Fields

The Sock entity has two required constructor parameters that are not in denormalization groups:

1. **identifier** (Identifier): Should be auto-generated during creation
2. **name** (NonEmptyString): Should be auto-generated (as per TODO comment in CreateSockProcessor)

For the POST endpoint to work correctly, one of the following must be implemented:

**Option A: Custom Denormalizer** (Recommended)
```php
// src/Drawer/Infrastructure/Symfony/Serializer/SockDenormalizer.php
final class SockDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Sock
    {
        return new Sock(
            identifier: new UuidIdentifier(), // Auto-generate
            emailAddress: new EmailAddress($data['emailAddress']),
            name: new NonEmptyString($this->generateUniqueName()), // Auto-generate
            style: null,
        );
    }

    private function generateUniqueName(): string
    {
        // Implementation: e.g., faker->unique()->firstName()
    }
}
```

**Option B: Constructor Default Values**
```php
public function __construct(
    private readonly Identifier $identifier = new UuidIdentifier(),
    // ... other params
    private readonly NonEmptyString $name = new NonEmptyString('GeneratedName'),
)
```
However, this approach is less flexible and doesn't support unique name generation.

**Current Test Assumption**: Tests assume the implementation will auto-generate these fields. If not implemented, the `itCreatesASock` test will fail with a construction error.

## Expected Behavior

All tests should pass when:
1. Database is properly configured and accessible
2. Doctrine migrations are up to date
3. All custom normalizers and Doctrine types are registered
4. Security configuration is correct
5. API Platform is properly configured
6. **Auto-generation logic for identifier and name is implemented** (see Architectural Considerations above)

## Notes

- Tests use `ResetDatabase` trait to ensure clean state between tests
- `Factories` trait enables Zenstruck Foundry factory usage
- Each test is isolated and can run independently
- No actual JWT tokens are used in tests (loginUser simulates authentication)
- Tests verify both happy paths and error scenarios
