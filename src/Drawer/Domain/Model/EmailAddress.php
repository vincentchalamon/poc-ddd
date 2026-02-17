<?php

declare(strict_types=1);

namespace App\Drawer\Domain\Model;

use App\Drawer\Domain\Exception\EmailAddressException;
use App\Shared\Domain\Text\NonEmptyString;

use function Symfony\Component\String\u;

/**
 * @extends NonEmptyString
 */
final readonly class EmailAddress extends NonEmptyString
{
    private string $domain;
    private string $topLevelDomain;

    public function __construct(
        string $emailAddress,
    ) {
        parent::__construct($emailAddress);

        if (!filter_var($emailAddress, \FILTER_VALIDATE_EMAIL)) {
            throw EmailAddressException::create($emailAddress);
        }

        $this->domain = u($emailAddress)->after('@')->toString();
        $this->topLevelDomain = u($this->domain)->afterLast('.')->toString();
    }

    public function domain(): string
    {
        return $this->domain;
    }

    public function topLevelDomain(): string
    {
        return $this->topLevelDomain;
    }
}
