<?php

declare(strict_types=1);

namespace App\Laundromat\Infrastructure\Doctrine\Entity;

use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Domain\Text\NonEmptyString;
use App\Shared\Infrastructure\Identifier\UuidIdentifier;
use BcMath\Number;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

// todo how to handle duplicate entities on same table (cf. Drawer/Sock) but here with only 1 writable property? (credits)
#[ORM\Entity]
class Sock
{
    #[ORM\Id]
    #[ORM\Column(type: UuidIdentifier::class, unique: true)]
    public Identifier $identifier;

    #[ORM\Column(type: NonEmptyString::class)]
    public NonEmptyString $name;

    #[ORM\Column(type: Types::NUMBER)]
    public Number $credits;
}
