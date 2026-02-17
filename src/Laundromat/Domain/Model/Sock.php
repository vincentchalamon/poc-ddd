<?php

declare(strict_types=1);

namespace App\Laundromat\Domain\Model;

use App\Laundromat\Domain\Exception\SockException;
use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Domain\Text\NonEmptyString;
use BcMath\Number;

/**
 * A local representation of a {@see \App\Drawer\Domain\Model\Sock} in the laundromat.
 */
final readonly class Sock implements \Stringable
{
    public function __construct(
        private Identifier $identifier,
        private NonEmptyString $name,
        private Number $credits,
    ) {
    }

    #[\Override]
    public function __toString(): string
    {
        return (string) $this->name();
    }

    public function identifier(): Identifier
    {
        return $this->identifier;
    }

    public function name(): NonEmptyString
    {
        return $this->name;
    }

    public function credits(): Number
    {
        return $this->credits;
    }

    public function increaseCredits(Number|string|int $credits): void
    {
        $this->credits->add($credits);
    }

    public function decreaseCredits(Number|string|int $credits): void
    {
        /* @see https://www.php.net/manual/fr/bcmath-number.compare.php */
        if (-1 === $this->credits->compare($credits)) {
            throw SockException::unsufficientCredits($credits, $this->credits);
        }

        $this->credits->sub($credits);
    }
}
