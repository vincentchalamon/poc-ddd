<?php

declare(strict_types=1);

namespace App\Utils\PHPStan\Tests\Rules\Fixtures\Application\State\Provider;

use App\Utils\PHPStan\Tests\Rules\Fixtures\Domain\Repository\FooRepositoryInterface;

final readonly class FooProvider
{
    public function __construct(
        private FooRepositoryInterface $repository,
    ) {
    }

    public function provide(): iterable
    {
        return $this->repository->all();
    }
}
