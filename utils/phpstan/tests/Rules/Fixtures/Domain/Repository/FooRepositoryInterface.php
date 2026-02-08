<?php

declare(strict_types=1);

namespace App\Utils\PHPStan\Tests\Rules\Fixtures\Domain\Repository;

interface FooRepositoryInterface
{
    public function all(): iterable;
}
