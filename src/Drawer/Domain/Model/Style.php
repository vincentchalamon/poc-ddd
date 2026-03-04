<?php

declare(strict_types=1);

namespace App\Drawer\Domain\Model;

use ApiPlatform\Metadata\ApiProperty;
use App\Drawer\Domain\Exception\StyleException;
use App\Drawer\Infrastructure\Doctrine\DBAL\Types\KeywordsType;
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

        #[ApiProperty(
            jsonldContext: [
                '@type' => 'schema:Text',
                '@container' => '@list',
            ]
        )]
        #[Assert\Count(min: 1, minMessage: 'At least one keyword is required.', groups: ['sock:update'])]
        #[Groups(['sock:read', 'sock:update'])]
        #[ORM\Column(type: KeywordsType::class)]
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

    public function size(): ?Size
    {
        return $this->size;
    }

    public function description(): ?NonEmptyString
    {
        return $this->description;
    }

    /**
     * @return array<NonEmptyString>|null
     */
    public function keywords(): ?array
    {
        return $this->keywords;
    }

    public function location(): ?Location
    {
        return $this->location;
    }
}
