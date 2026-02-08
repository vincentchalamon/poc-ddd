<?php

namespace App\Utils\Rector\Tests\Rector\RenameHandlerMethodNameToInvokeRector\Fixture;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final readonly class CreateFooHandler
{
    #[AsMessageHandler]
    public function createBar(FooCreated $event): void
    {
    }
}

?>
-----
<?php

namespace App\Utils\Rector\Tests\Rector\RenameHandlerMethodNameToInvokeRector\Fixture;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final readonly class CreateFooHandler
{
    #[AsMessageHandler]
    public function __invoke(FooCreated $event): void
    {
    }
}

?>
