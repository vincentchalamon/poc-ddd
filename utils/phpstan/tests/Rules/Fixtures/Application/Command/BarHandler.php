<?php

declare(strict_types=1);

namespace App\Utils\PHPStan\Tests\Rules\Fixtures\Application\Command;

use App\Utils\PHPStan\Tests\Rules\Fixtures\Infrastructure\Persistence\Repository\FooRepository;

final readonly class BarHandler
{
    public function __construct(
        private FooRepository $repository,
    ) {
    }

    public function __invoke(): void
    {
        $this->repository->all();
    }
}
