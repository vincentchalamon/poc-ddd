<?php

declare(strict_types=1);

namespace App\Laundromat\Application\Model;

use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Domain\Text\NonEmptyString;

final readonly class Sock
{
    public Identifier $identifier;

    public NonEmptyString $name;
}
