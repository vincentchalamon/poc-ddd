<?php

declare(strict_types=1);

namespace App\Utils\PHPStan\Tests\Rules\Fixtures\Application\State\Provider;

use App\Utils\PHPStan\Tests\Rules\Fixtures\Infrastructure\Persistence\Repository\FooRepository;

final readonly class BarProvider
{
    public function __construct(
        private FooRepository $repository,
    ) {
    }

    public function provide(): iterable
    {
        return $this->repository->all();
    }
}
