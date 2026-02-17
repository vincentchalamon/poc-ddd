<?php

declare(strict_types=1);

namespace App\Laundromat\Infrastructure\ApiPlatform\ApiResource;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Laundromat\Infrastructure\ApiPlatform\State\GetSockProvider;
use App\Laundromat\Infrastructure\ApiPlatform\State\ListSocksProvider;
use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Domain\Text\NonEmptyString;

#[GetCollection(
    uriTemplate: '/laundromat',
    security: 'is_granted("ROLE_USER")',
    provider: ListSocksProvider::class,
)]
#[Get(
    uriTemplate: '/laundromat/socks/{identifier}',
    // todo add pairing security
    security: 'is_granted("ROLE_USER") and object.getIdentifier() == user.getIdentifier()',
    provider: GetSockProvider::class,
)]
final readonly class Sock
{
    public Identifier $identifier;

    public NonEmptyString $name;

    // todo add Style
}
