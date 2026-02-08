<?php

declare(strict_types=1);

namespace App\Utils\PHPStan\Tests\Rules;

use App\Utils\PHPStan\Rules\RepositoryInjectionRule;
use App\Utils\PHPStan\Tests\Rules\Fixtures\Application\State\Provider\BarProvider;
use App\Utils\PHPStan\Tests\Rules\Fixtures\Application\State\Provider\FooProvider;
use PHPStan\Rules\Rule as TRule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class RepositoryInjectionRuleTest extends RuleTestCase
{
    #[\Override]
    protected function getRule(): TRule
    {
        return new RepositoryInjectionRule();
    }

    #[Test]
    #[DataProvider(methodName: 'getInvalidServices')]
    public function itFailsIfRepositoryIsInjectedOutOfAHandler(string $file, string $error, int $line): void
    {
        $this->analyse([$file], [[$error, $line]]);
    }

    public static function getInvalidServices(): iterable
    {
        yield 'provider with repository interface' => [
            __DIR__.'/Fixtures/Application/State/Provider/FooProvider.php',
            \sprintf('Property %s::$repository is not allowed here. Repositories are only injectable in Command, Event and Query handlers, and Console Commands.', FooProvider::class),
            12,
        ];
        yield 'provider with repository final class' => [
            __DIR__.'/Fixtures/Application/State/Provider/BarProvider.php',
            \sprintf('Property %s::$repository is not allowed here. Repositories are only injectable in Command, Event and Query handlers, and Console Commands.', BarProvider::class),
            12,
        ];
    }

    #[Test]
    #[DataProvider(methodName: 'getValidServices')]
    public function itSucceedsIfRepositoryIsInjectedInAHandler(string $file): void
    {
        $this->analyse([$file], []);
    }

    public static function getValidServices(): iterable
    {
        yield 'command handler with repository interface' => [__DIR__.'/Fixtures/Application/Command/FooHandler.php'];
        yield 'command handler with repository final class' => [__DIR__.'/Fixtures/Application/Command/BarHandler.php'];

        yield 'event handler with repository interface' => [__DIR__.'/Fixtures/Application/Event/FooHandler.php'];
        yield 'event handler with repository final class' => [__DIR__.'/Fixtures/Application/Event/BarHandler.php'];

        yield 'query handler with repository interface' => [__DIR__.'/Fixtures/Application/Query/FooHandler.php'];
        yield 'query handler with repository final class' => [__DIR__.'/Fixtures/Application/Query/BarHandler.php'];

        yield 'console command with repository interface' => [__DIR__.'/Fixtures/Infrastructure/Console/FooCommand.php'];
        yield 'console command with repository final class' => [__DIR__.'/Fixtures/Infrastructure/Console/BarCommand.php'];
    }
}
