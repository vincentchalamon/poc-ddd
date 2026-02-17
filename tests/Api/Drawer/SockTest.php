<?php

declare(strict_types=1);

namespace App\Tests\Api\Drawer;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Drawer\Infrastructure\Symfony\Security\Core\User\User;
use App\Tests\Api\Drawer\Factory\SockFactory;
use App\Tests\Api\Drawer\Factory\StyleFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class SockTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    #[Test]
    public function itValidatesOpenAPISchema(): void
    {
        $response = static::createClient()->request('GET', '/docs.jsonopenapi', [
            'headers' => [
                'Accept' => 'application/vnd.openapi+json',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/vnd.openapi+json; charset=utf-8');

        $openApiDoc = $response->toArray();

        // Validate that Sock-related paths are present
        self::assertArrayHasKey('paths', $openApiDoc);
        self::assertArrayHasKey('/socks', $openApiDoc['paths']);
        self::assertArrayHasKey('/socks/{identifier}', $openApiDoc['paths']);

        // Validate Sock operations
        self::assertArrayHasKey('post', $openApiDoc['paths']['/socks']);
        self::assertArrayHasKey('get', $openApiDoc['paths']['/socks/{identifier}']);
        self::assertArrayHasKey('patch', $openApiDoc['paths']['/socks/{identifier}']);

        // Validate components contain Sock schemas
        self::assertArrayHasKey('components', $openApiDoc);
        self::assertArrayHasKey('schemas', $openApiDoc['components']);

        // Validate against JSON schema
        self::assertMatchesJsonSchema((string) file_get_contents(__DIR__.'/schemas/openapi-sock.json'));
    }

    #[Test]
    public function itCreatesASock(): void
    {
        $email = 'john.doe@example.com';

        $response = static::createClient()->request('POST', '/socks', [
            'json' => [
                'emailAddress' => $email,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'emailAddress' => $email,
        ]);
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $response->toArray()['identifier']);
        self::assertArrayHasKey('name', $response->toArray());
        self::assertNull($response->toArray()['style']);

        // Validate response against JSON schema
        self::assertMatchesJsonSchema((string) file_get_contents(__DIR__.'/schemas/created.json'));
    }

    /**
     * @param array<string, mixed>      $payload
     * @param array<string, mixed>|null $expectedViolation
     */
    #[Test]
    #[DataProvider('invalidSockCreationDataProvider')]
    public function itFailsToCreateASockWithInvalidData(array $payload, ?array $expectedViolation = null): void
    {
        static::createClient()->request('POST', '/socks', [
            'json' => $payload,
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        if (null !== $expectedViolation) {
            self::assertJsonContains([
                'violations' => [$expectedViolation],
            ]);
        }

        // Validate validation error response against JSON schema
        self::assertMatchesJsonSchema((string) file_get_contents(__DIR__.'/schemas/validation-error.json'));
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>, expectedViolation: array<string, mixed>|null}>
     */
    public static function invalidSockCreationDataProvider(): iterable
    {
        yield 'invalid email format' => [
            'payload' => ['emailAddress' => 'not-an-email'],
            'expectedViolation' => [
                'propertyPath' => 'emailAddress',
                'message' => 'Email address "not-an-email" is invalid.',
            ],
        ];

        yield 'missing email address' => [
            'payload' => [],
            'expectedViolation' => [
                'propertyPath' => 'emailAddress',
                'message' => 'This value should not be null.',
            ],
        ];
    }

    #[Test]
    #[DataProvider('unauthorizedAccessDataProvider')]
    public function itDeniesUnauthorizedAccess(string $method, string $uriCallback, bool $authenticate, int $expectedStatusCode): void
    {
        $sock = SockFactory::createOne();
        $anotherSock = SockFactory::createOne();

        $uri = match ($uriCallback) {
            'ownSock' => '/socks/'.$sock->identifier(),
            'anotherSock' => '/socks/'.$anotherSock->identifier(),
            default => throw new \InvalidArgumentException('Invalid URI callback'),
        };

        $client = static::createClient();

        if ($authenticate) {
            $client->loginUser(new User($sock));
        }

        $payload = match ($method) {
            'PATCH' => [
                'json' => [
                    'style' => [
                        'size' => 150,
                        'description' => 'Test',
                        'keywords' => ['test'],
                        'location' => [
                            'latitude' => 45.0,
                            'longitude' => 10.0,
                        ],
                    ],
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ],
            ],
            default => [],
        };

        $client->request($method, $uri, $payload);

        self::assertResponseStatusCodeSame($expectedStatusCode);
    }

    /**
     * @return iterable<string, array{method: string, uriCallback: string, authenticate: bool, expectedStatusCode: int}>
     */
    public static function unauthorizedAccessDataProvider(): iterable
    {
        yield 'GET another sock without authentication' => [
            'method' => 'GET',
            'uriCallback' => 'anotherSock',
            'authenticate' => false,
            'expectedStatusCode' => Response::HTTP_UNAUTHORIZED,
        ];

        yield 'GET another sock with authentication' => [
            'method' => 'GET',
            'uriCallback' => 'anotherSock',
            'authenticate' => true,
            'expectedStatusCode' => Response::HTTP_FORBIDDEN,
        ];

        yield 'PATCH another sock with authentication' => [
            'method' => 'PATCH',
            'uriCallback' => 'anotherSock',
            'authenticate' => true,
            'expectedStatusCode' => Response::HTTP_FORBIDDEN,
        ];

        yield 'PATCH own sock without authentication' => [
            'method' => 'PATCH',
            'uriCallback' => 'ownSock',
            'authenticate' => false,
            'expectedStatusCode' => Response::HTTP_UNAUTHORIZED,
        ];
    }

    #[Test]
    public function itGetsItsSock(): void
    {
        $sock = SockFactory::createOne();

        $client = static::createClient();
        $client->loginUser(new User($sock));

        $client->request('GET', '/socks/'.$sock->identifier());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        self::assertJsonContains([
            'identifier' => (string) $sock->identifier(),
            'emailAddress' => (string) $sock->emailAddress(),
            'name' => (string) $sock->name(),
            'style' => null,
        ]);

        // Validate response against JSON schema
        self::assertMatchesJsonSchema((string) file_get_contents(__DIR__.'/schemas/item.json'));
    }

    #[Test]
    public function itGetsItsSockWithStyle(): void
    {
        $style = StyleFactory::createOne();
        $sock = SockFactory::new()->withStyle($style)->create();

        $client = static::createClient();
        $client->loginUser(new User($sock));

        $response = $client->request('GET', '/socks/'.$sock->identifier());

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'identifier' => (string) $sock->identifier(),
            'emailAddress' => (string) $sock->emailAddress(),
            'name' => (string) $sock->name(),
        ]);

        $responseData = $response->toArray();
        self::assertIsArray($responseData['style']);
        self::assertArrayHasKey('size', $responseData['style']);
        self::assertArrayHasKey('description', $responseData['style']);
        self::assertArrayHasKey('keywords', $responseData['style']);
        self::assertArrayHasKey('location', $responseData['style']);

        // Validate response against JSON schema
        self::assertMatchesJsonSchema((string) file_get_contents(__DIR__.'/schemas/item.json'));
    }

    #[Test]
    public function itCannotGetNonExistentSock(): void
    {
        $sock = SockFactory::createOne();

        $client = static::createClient();
        $client->loginUser(new User($sock));

        $client->request('GET', '/socks/018e8f5e-0000-7000-8000-000000000000');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function itUpdatesItsSock(): void
    {
        $sock = SockFactory::createOne();

        $client = static::createClient();
        $client->loginUser(new User($sock));

        $client->request('PATCH', '/socks/'.$sock->identifier(), [
            'json' => [
                'style' => [
                    'size' => 175.5,
                    'description' => 'Athletic and comfortable',
                    'keywords' => ['sporty', 'casual', 'comfortable'],
                    'location' => [
                        'latitude' => 48.8566,
                        'longitude' => 2.3522,
                    ],
                ],
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'identifier' => (string) $sock->identifier(),
            'style' => [
                'size' => 175.5,
                'description' => 'Athletic and comfortable',
                'keywords' => ['sporty', 'casual', 'comfortable'],
                'location' => [
                    'latitude' => 48.8566,
                    'longitude' => 2.3522,
                ],
            ],
        ]);

        // Validate response against JSON schema
        self::assertMatchesJsonSchema((string) file_get_contents(__DIR__.'/schemas/item.json'));
    }

    /**
     * @param array<string, mixed>      $stylePayload
     * @param array<string, mixed>|null $expectedViolation
     */
    #[Test]
    #[DataProvider('invalidStyleUpdateDataProvider')]
    public function itFailsToUpdateSockWithInvalidStyle(array $stylePayload, ?array $expectedViolation = null): void
    {
        $sock = SockFactory::createOne();

        $client = static::createClient();
        $client->loginUser(new User($sock));

        $client->request('PATCH', '/socks/'.$sock->identifier(), [
            'json' => [
                'style' => $stylePayload,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        if (null !== $expectedViolation) {
            self::assertJsonContains([
                'violations' => [$expectedViolation],
            ]);
        }

        // Validate validation error response against JSON schema
        self::assertMatchesJsonSchema((string) file_get_contents(__DIR__.'/schemas/validation-error.json'));
    }

    /**
     * @return iterable<string, array{stylePayload: array<string, mixed>, expectedViolation: array<string, mixed>|null}>
     */
    public static function invalidStyleUpdateDataProvider(): iterable
    {
        yield 'size exceeds maximum (250cm)' => [
            'stylePayload' => [
                'size' => 300,
                'description' => 'Test description',
                'keywords' => ['test'],
                'location' => [
                    'latitude' => 45.0,
                    'longitude' => 10.0,
                ],
            ],
            'expectedViolation' => null, // Domain exception, not validation
        ];

        yield 'size below minimum (100cm)' => [
            'stylePayload' => [
                'size' => 50,
                'description' => 'Test description',
                'keywords' => ['test'],
                'location' => [
                    'latitude' => 45.0,
                    'longitude' => 10.0,
                ],
            ],
            'expectedViolation' => null, // Domain exception, not validation
        ];

        yield 'latitude exceeds maximum (90)' => [
            'stylePayload' => [
                'size' => 150,
                'description' => 'Test description',
                'keywords' => ['test'],
                'location' => [
                    'latitude' => 100,
                    'longitude' => 10.0,
                ],
            ],
            'expectedViolation' => null, // Domain exception, not validation
        ];

        yield 'longitude below minimum (-180)' => [
            'stylePayload' => [
                'size' => 150,
                'description' => 'Test description',
                'keywords' => ['test'],
                'location' => [
                    'latitude' => 45.0,
                    'longitude' => -200,
                ],
            ],
            'expectedViolation' => null, // Domain exception, not validation
        ];

        yield 'empty keywords array' => [
            'stylePayload' => [
                'size' => 150,
                'description' => 'Test description',
                'keywords' => [],
                'location' => [
                    'latitude' => 45.0,
                    'longitude' => 10.0,
                ],
            ],
            'expectedViolation' => [
                'propertyPath' => 'style.keywords',
                'message' => 'At least one keyword is required.',
            ],
        ];

        yield 'incomplete style (only size)' => [
            'stylePayload' => [
                'size' => 150,
            ],
            'expectedViolation' => null, // Multiple violations expected
        ];

        yield 'missing size' => [
            'stylePayload' => [
                'description' => 'Test description',
                'keywords' => ['test'],
                'location' => [
                    'latitude' => 45.0,
                    'longitude' => 10.0,
                ],
            ],
            'expectedViolation' => null, // Validation violation expected
        ];
    }

    #[Test]
    public function itUpdatesStyleMultipleTimes(): void
    {
        $sock = SockFactory::createOne();

        $client = static::createClient();
        $client->loginUser(new User($sock));

        // First update
        $client->request('PATCH', '/socks/'.$sock->identifier(), [
            'json' => [
                'style' => [
                    'size' => 160.0,
                    'description' => 'First update',
                    'keywords' => ['first'],
                    'location' => [
                        'latitude' => 40.0,
                        'longitude' => 20.0,
                    ],
                ],
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseIsSuccessful();

        // Second update
        $client->request('PATCH', '/socks/'.$sock->identifier(), [
            'json' => [
                'style' => [
                    'size' => 180.0,
                    'description' => 'Second update',
                    'keywords' => ['second', 'updated'],
                    'location' => [
                        'latitude' => 50.0,
                        'longitude' => 30.0,
                    ],
                ],
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'style' => [
                'size' => 180.0,
                'description' => 'Second update',
                'keywords' => ['second', 'updated'],
            ],
        ]);

        // Validate response against JSON schema
        self::assertMatchesJsonSchema((string) file_get_contents(__DIR__.'/schemas/item.json'));
    }
}
