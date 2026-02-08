<?php

namespace App\Utils\Rector\Tests\Rector\RenameHandlerMethodNameToInvokeRector\Fixture;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final readonly class GetFooHandler
{
    #[AsMessageHandler]
    public function getFoo(GetFoo $query): object
    {
        return new \stdClass();
    }
}

?>
-----
<?php

namespace App\Utils\Rector\Tests\Rector\RenameHandlerMethodNameToInvokeRector\Fixture;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final readonly class GetFooHandler
{
    #[AsMessageHandler]
    public function __invoke(GetFoo $query): object
    {
        return new \stdClass();
    }
}

?>
