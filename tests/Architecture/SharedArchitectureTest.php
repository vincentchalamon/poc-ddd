<?php

declare(strict_types=1);

namespace App\Tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Attributes\TestRule;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;
use Symfony\Component\String\UnicodeString;

final class SharedArchitectureTest extends ArchitectureTestCase
{
    #[TestRule]
    public function domain_independence(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(namespace: 'App\Shared\Domain'))
            ->canOnlyDependOn()
            ->classes(...[
                ...$this->domain(),
                /* @see DomainNotFoundThrowable */
                Selector::classname(fqcn: WithHttpStatus::class),
                Selector::classname(fqcn: Response::class),
                Selector::classname(fqcn: UnicodeString::class),
            ]);
    }

    #[TestRule]
    public function application_independence(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(namespace: 'App\Shared\Application'))
            ->canOnlyDependOn()
            ->classes(...$this->application());
    }

    #[TestRule]
    public function infrastructure_independence(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(namespace: 'App\Shared\Infrastructure'))
            ->canOnlyDependOn()
            ->classes(...$this->infrastructure());
    }
}
