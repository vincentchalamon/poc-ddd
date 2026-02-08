<?php

declare(strict_types=1);

namespace App\Utils\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Checks that repositories are only injected in Command/Event/Query handlers and Console Commands.
 *
 * @implements Rule<ClassPropertyNode>
 */
final readonly class RepositoryInjectionRule implements Rule
{
    #[\Override]
    public function getNodeType(): string
    {
        return ClassPropertyNode::class;
    }

    /**
     * @param ClassPropertyNode $node
     *
     * @throws ShouldNotHappenException
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        // Repository is injectable in Command, Event and Query handlers, and other repositories (special use-case)
        $className = $scope->getClassReflection()?->getName();

        // Should not happen
        if (in_array($className, [null, '', '0'], true)) {
            return [];
        }

        // If the class name matches, no need to check anything
        if (preg_match('/\\\(?:Command|Event|Query)\\\.*Handler|Repository$|\\\Console\\\.+Command$/', $className)) {
            return [];
        }

        $type = $node->getNativeTypeNode();
        // It is not an object
        if (!$type instanceof Name || !$type->isFullyQualified()) {
            return [];
        }

        // The argument is not a repository interface
        // ...nor a repository final class (nice try Bobby!)
        if (!preg_match('/Repository(?:Interface)?$/', $type->toString())) {
            return [];
        }

        return [
            RuleErrorBuilder::message(\sprintf(
                'Property %s::$%s is not allowed here. Repositories are only injectable in Command, Event and Query handlers, and Console Commands.',
                $className,
                $node->getName()
            ))
                ->file($scope->getFile())
                ->line($node->getStartLine())
                ->identifier('app.repositoryInjection')
                ->build(),
        ];
    }
}
