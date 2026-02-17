<?php

declare(strict_types=1);

namespace App\Drawer\Domain\Model;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Drawer\Infrastructure\ApiPlatform\State\CreateSockProcessor;
use App\Drawer\Infrastructure\ApiPlatform\State\CreateSockProvider;
use App\Drawer\Infrastructure\ApiPlatform\State\GetSockProvider;
use App\Drawer\Infrastructure\ApiPlatform\State\UpdateSockProcessor;
use App\Shared\Domain\Identifier\Identifier;
use App\Shared\Domain\Text\NonEmptyString;
use App\Shared\Infrastructure\Identifier\UuidIdentifier;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/socks',
            denormalizationContext: ['groups' => ['sock:create']],
            validationContext: ['groups' => ['sock:create']],
            read: true,
            provider: CreateSockProvider::class,
            processor: CreateSockProcessor::class,
        ),
        new Get(
            uriTemplate: '/socks/{identifier}',
            security: 'is_granted("ROLE_USER") and user.getUserIdentifier() == object.emailAddress()',
            provider: GetSockProvider::class,
        ),
        new Patch(
            uriTemplate: '/socks/{identifier}',
            denormalizationContext: ['groups' => ['sock:update']],
            security: 'is_granted("ROLE_USER") and user.getUserIdentifier() == object.emailAddress()',
            validationContext: ['groups' => ['sock:update']],
            provider: GetSockProvider::class,
            processor: UpdateSockProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['sock:read']],
)]
#[ORM\Entity]
class Sock implements \Stringable
{
    public function __construct(
        #[Groups(['sock:read'])]
        #[ORM\Id]
        #[ORM\Column(type: UuidIdentifier::class, unique: true)]
        private readonly Identifier $identifier,

        #[Assert\NotNull(groups: ['sock:create'])]
        #[Groups(['sock:read', 'sock:create'])]
        #[ORM\Column(type: EmailAddress::class)]
        private readonly EmailAddress $emailAddress,

        #[Groups(['sock:read'])]
        #[ORM\Column(type: NonEmptyString::class)]
        private readonly NonEmptyString $name,

        #[Assert\NotNull(groups: ['sock:update'])]
        #[Groups(['sock:read', 'sock:update'])]
        #[ORM\Embedded(class: Style::class)]
        private ?Style $style = null,
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

    public function emailAddress(): EmailAddress
    {
        return $this->emailAddress;
    }

    public function name(): NonEmptyString
    {
        return $this->name;
    }

    public function hasStyle(): bool
    {
        return $this->style instanceof Style;
    }

    public function style(): ?Style
    {
        return $this->style;
    }

    public function fillStyle(Style $style): void
    {
        $this->style = $style;
    }
}
