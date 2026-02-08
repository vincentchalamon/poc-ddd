<?php

declare(strict_types=1);

namespace App\Utils\PHPStan\Tests\Rules\Fixtures\Infrastructure\Persistence\Repository;

use App\Utils\PHPStan\Tests\Rules\Fixtures\Domain\Repository\FooRepositoryInterface;

final readonly class FooRepository implements FooRepositoryInterface
{
    #[\Override]
    public function all(): iterable
    {
        return [];
    }
}
