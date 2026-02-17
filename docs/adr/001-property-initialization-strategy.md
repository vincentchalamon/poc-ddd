# Initialization Strategy for Nullable Typed Properties in Rich Domain Models

* **Status:** Accepted
* **Context:** `Drawer` Bounded Context
* **Date:** 2026-02-17
* **Tags:** #php8 #doctrine #ddd #resilience

## Context and Problem Statement

In our Rich Domain Models (specifically the `Sock` entity), we utilize **PHP 8 Constructor Property Promotion** to reduce boilerplate code. These models serve as both Domain Entities and Doctrine Infrastructure Entities (via Attributes).

We encountered a critical runtime error when retrieving entities from the database:
`Error: Typed property App\Drawer\Domain\Model\Sock::$style must not be accessed before initialization`.

### Technical Root Cause

1. **PHP Behavior:** When using Constructor Property Promotion, default values are defined in the constructor signature (e.g., `private ?Style $style = null`).
2. **Doctrine Hydration:** Doctrine uses `ReflectionClass::newInstanceWithoutConstructor()` to instantiate objects before populating them.
3. **The Conflict:** When bypassing the constructor, PHP **does not apply** the default values defined in the constructor arguments.
4. **Result:** If the database columns corresponding to an Embeddable or a nullable relation are `NULL`, Doctrine performs no action on that property. The property remains in an **uninitialized state** (technically distinct from `null`).
5. **Consequence:** Any access to this property (e.g., via a getter or the Serializer) triggers a fatal PHP error, rendering the domain object fragile depending on how it was instantiated (via `new` vs via Reflection).

We need a strategy to ensure our Domain Models remain robust and valid PHP objects regardless of their instantiation method, without leaking excessive infrastructure complexity into the Domain.

## Decision Drivers

* **Robustness:** A Domain object should never be in a technically invalid state (uninitialized properties) after instantiation, regardless of the factory mechanism (Constructor or Reflection).
* **Pragmatism:** Avoid over-engineering infrastructure solutions (listeners) for standard language behaviors.
* **Developer Experience:** Keep the Domain Model readable and maintainable.
* **Performance:** Avoid runtime reflection overhead where possible.

## Considered Options

### 1. Infrastructure Event Listener (`PostLoad`)

Create a Doctrine `PostLoad` listener that uses Reflection to detect uninitialized properties and force them to `null` after hydration.

* *Pros:* Keeps the Domain Model syntax strictly using Constructor Property Promotion.
* *Cons:* High "Magical" complexity; Runtime performance cost (Reflection on every load); Hides the fragility of the model behind an infrastructure curtain.

### 2. Getter Logic Checks

Modify getters to check initialization state (e.g., `return isset($this->style) ? $this->style : null;`).

* *Pros:* Solves the access error.
* *Cons:* Pollutes the Domain logic with technical defensive coding; does not solve the underlying state issue (the property is still uninitialized internally).

### 3. Explicit Property Definition (Un-promoting)

Remove the specific nullable property from Constructor Promotion and define it in the class body with an explicit default value.

## Decision Outcome

We chose **Option 3: Explicit Property Definition (Un-promoting)**.

We will strictly define nullable properties with default values *outside* the constructor signature when those properties run the risk of being ignored by Doctrine hydration (typically optional Embeddables or Relations).

### Implementation Pattern

**Before (Fragile):**

```php
class Sock {
    public function __construct(
        // ...
        private ?Style $style = null, // Ignored by Reflection instantiation
    ) {}
}

```

**After (Robust):**

```php
class Sock {
    // Explicit definition ensures PHP initializes this to null
    // even when newInstanceWithoutConstructor() is used.
    private ?Style $style = null;

    public function __construct(
        // ...
        ?Style $style = null,
    ) {
        $this->style = $style;
    }
}

```

## Consequences

### Positive

* **Resilience:** The Domain Model is now "safe by default". It behaves consistently whether instantiated via `new Sock()` (Unit Tests, Command Handlers) or Doctrine (Repositories).
* **Standard Compliance:** Relies on standard PHP language features (property defaults) rather than framework hooks.
* **Performance:** Zero runtime overhead compared to Event Listeners.

### Negative

* **Verbosity:** Slightly more verbose code than pure Constructor Property Promotion (requires property declaration + assignment in constructor).

## References

* [PHP RFC: Constructor Property Promotion]()
* [Doctrine ORM: Instantiation and Hydration]()
