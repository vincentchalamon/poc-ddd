<?php

declare(strict_types=1);

namespace App\Utils\Rector\Tests\Rector\RenameHandlerMethodNameToInvokeRector;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameHandlerMethodNameToInvokeRectorTest extends AbstractRectorTestCase
{
    #[Test]
    #[DataProvider(methodName: 'provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): \Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture');
    }

    #[\Override]
    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/configured_rule.php';
    }
}
