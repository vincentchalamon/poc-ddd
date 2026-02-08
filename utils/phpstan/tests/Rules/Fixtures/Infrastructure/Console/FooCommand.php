<?php

declare(strict_types=1);

namespace App\Utils\PHPStan\Tests\Rules\Fixtures\Infrastructure\Console;

use App\Utils\PHPStan\Tests\Rules\Fixtures\Domain\Repository\FooRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FooCommand extends Command
{
    public function __construct(
        private readonly FooRepositoryInterface $repository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->repository->all();

        return Command::SUCCESS;
    }
}
