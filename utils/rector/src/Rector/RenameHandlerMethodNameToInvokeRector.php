<?php

declare(strict_types=1);

namespace App\Utils\Rector\Rector;

use App\Utils\Rector\Tests\Rector\RenameHandlerMethodNameToInvokeRector\RenameHandlerMethodNameToInvokeRectorTest;
use PhpParser\Node;
use Rector\Rector\AbstractRector;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @see RenameHandlerMethodNameToInvokeRectorTest
 */
final class RenameHandlerMethodNameToInvokeRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    #[\Override]
    public function getNodeTypes(): array
    {
        return [Node\Stmt\ClassMethod::class];
    }

    /**
     * @param Node\Stmt\ClassMethod $node
     */
    #[\Override]
    public function refactor(Node $node): ?Node
    {
        if ('__invoke' === $this->getName($node)) {
            return null;
        }

        foreach ($node->attrGroups as $attrGroupNode) {
            foreach ($attrGroupNode->attrs as $attrNode) {
                if (!is_a($attrNode->name->toString(), AsMessageHandler::class, true)) {
                    continue;
                }

                $node->name = new Node\Identifier('__invoke');

                return $node;
            }
        }

        return null;
    }
}
