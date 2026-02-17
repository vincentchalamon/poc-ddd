<?php

declare(strict_types=1);

namespace App\Drawer\Domain\Model;

use App\Drawer\Domain\Exception\StyleException;
use App\Shared\Domain\Text\NonEmptyString;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class Style
{
    /**
     * @param iterable<NonEmptyString> $keywords
     */
    public function __construct(
        #[Assert\NotNull(groups: ['sock:update'])]
        #[Groups(['sock:read', 'sock:update'])]
        #[ORM\Column(type: Size::class)]
        private Size $size,

        #[Assert\NotNull(groups: ['sock:update'])]
        #[Groups(['sock:read', 'sock:update'])]
        #[ORM\Column(type: NonEmptyString::class)]
        private NonEmptyString $description,

        /**
         * @var array<array-key, NonEmptyString>
         */
        #[Assert\Count(min: 1, minMessage: 'At least one keyword is required.', groups: ['sock:update'])]
        #[Groups(['sock:read', 'sock:update'])]
        #[ORM\Column(type: Types::JSON)]
        private array $keywords,

        #[Assert\NotNull(groups: ['sock:update'])]
        #[Groups(['sock:read', 'sock:update'])]
        #[ORM\Column(type: Location::class)]
        private Location $location,
    ) {
        if (empty($this->keywords)) {
            throw StyleException::fromMissingKeywords();
        }

        if (count(array_filter($this->keywords, static fn ($keyword): bool => !$keyword instanceof NonEmptyString))) {
            throw new \LogicException('All keywords must be of type NonEmptyString');
        }
    }

    public function size(): Size
    {
        return $this->size;
    }

    public function description(): NonEmptyString
    {
        return $this->description;
    }

    /**
     * @return iterable<NonEmptyString>
     */
    public function keywords(): iterable
    {
        return $this->keywords;
    }

    public function location(): Location
    {
        return $this->location;
    }
}
