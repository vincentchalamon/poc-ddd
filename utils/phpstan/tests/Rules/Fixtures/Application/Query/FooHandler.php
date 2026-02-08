<?php

declare(strict_types=1);

namespace App\Utils\PHPStan\Tests\Rules\Fixtures\Application\Query;

use App\Utils\PHPStan\Tests\Rules\Fixtures\Domain\Repository\FooRepositoryInterface;

final readonly class FooHandler
{
    public function __construct(
        private FooRepositoryInterface $repository,
    ) {
    }

    public function __invoke(): void
    {
        $this->repository->all();
    }
}
