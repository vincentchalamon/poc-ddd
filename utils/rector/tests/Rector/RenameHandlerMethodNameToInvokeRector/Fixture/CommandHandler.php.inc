<?php

namespace App\Utils\Rector\Tests\Rector\RenameHandlerMethodNameToInvokeRector\Fixture;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final readonly class CreateFooHandler
{
    #[AsMessageHandler]
    public function createFoo(CreateFoo $command): void
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
    public function __invoke(CreateFoo $command): void
    {
    }
}

?>
