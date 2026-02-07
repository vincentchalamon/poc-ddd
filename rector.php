<?php

declare(strict_types=1);

use AE\Common\Infrastructure\ApiPlatform\Provider\PaginationContextProvider;
use AE\IdentityAccess\User\Infrastructure\OIDC\Repository\OidcInMemoryRepository;
use AE\Utils\Rector\Rector\ExceptionExtendsDomainExceptionRector;
use AE\Utils\Rector\Rector\PaginationFromContextCallRector;
use AE\Utils\Rector\Rector\RenameHandlerMethodNameToInvokeRector;
use AE\Utils\Rector\Rector\ReplacePutByPatchApiPlatformOperationRector;
use AE\Utils\Rector\Rector\SerializationGroupsNamingRector;
use AE\Utils\Rector\Rector\StringToFunctionSymfonyConfigParameterRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;
use Rector\DeadCode\Rector\If_\ReduceAlwaysFalseIfOrRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;
use Rector\Renaming\Rector\Class_\RenameAttributeRector;
use Rector\Renaming\ValueObject\RenameAttribute;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector;
use Rector\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Symfony\Component\Serializer\Annotation;
use Symfony\Component\Serializer\Attribute;

$rector = RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/fixtures',
        __DIR__.'/migrations',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/utils',
    ])
    ->withRootFiles()
    ->withPhpSets(
        php83: true,
    )
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::INSTANCEOF,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
        SetList::STRICT_BOOLEANS,
    ])
    ->withAttributesSets()
    ->withComposerBased(doctrine: true, phpunit: true)
    ->withConfiguredRule(RenameAttributeRector::class, [
        new RenameAttribute(Annotation\Context::class, Attribute\Context::class),
        new RenameAttribute(Annotation\DiscriminatorMap::class, Attribute\DiscriminatorMap::class),
        new RenameAttribute(Annotation\Groups::class, Attribute\Groups::class),
        new RenameAttribute(Annotation\Ignore::class, Attribute\Ignore::class),
        new RenameAttribute(Annotation\MaxDepth::class, Attribute\MaxDepth::class),
        new RenameAttribute(Annotation\SerializedName::class, Attribute\SerializedName::class),
        new RenameAttribute(Annotation\SerializedPath::class, Attribute\SerializedPath::class),
    ])
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
        RenameHandlerMethodNameToInvokeRector::class,
        ReplacePutByPatchApiPlatformOperationRector::class,
        SerializationGroupsNamingRector::class,
    ])
    ->withConfiguredRule(StringToFunctionSymfonyConfigParameterRector::class, [
        __DIR__.'/config/**/*',
    ])
    ->withConfiguredRule(PaginationFromContextCallRector::class, [
        OidcInMemoryRepository::class,
        PaginationContextProvider::class,
        __DIR__.'/tests/**/*',
    ])
    ->withConfiguredRule(ExceptionExtendsDomainExceptionRector::class, [
        __DIR__.'/src/**/*/Domain',
    ])
    ->withSkip([
        __DIR__.'/config/bundles.php',
        __DIR__.'/src/Brew/ApiPlatform/Metadata',
        AddMethodCallBasedStrictParamTypeRector::class => [
            __DIR__.'/src/Common/Domain/Money/Money.php',
        ],
        BooleanInIfConditionRuleFixerRector::class,
        BooleanInBooleanNotRuleFixerRector::class,
        EncapsedStringsToSprintfRector::class, // generates invalid modifications
        ExplicitBoolCompareRector::class,
        FirstClassCallableRector::class,
        NewlineAfterStatementRector::class,
        NewlineBeforeNewAssignSetRector::class,
        PrivatizeLocalGetterToPropertyRector::class, // remove this rules to allow using methods with validation instead of properties
        StringToFunctionSymfonyConfigParameterRector::class => [
            __DIR__.'/config/packages/cache.php',
        ],
        RemoveUnusedPublicMethodParameterRector::class => [
            __DIR__.'/src/**/*/Application/**/*Handler.php',
        ],
        ReduceAlwaysFalseIfOrRector::class => [
            __DIR__.'/src/Common/Infrastructure/Symfony/Serializer/Identifier/StringIdentifierNormalizer.php',
            __DIR__.'/src/Common/Infrastructure/Symfony/Serializer/Identifier/UuidIdentifierNormalizer.php',
        ],
    ])
;

$kernelFilename = __DIR__.'/var/cache/dev/AE_Common_Infrastructure_Symfony_KernelDevDebugContainer.xml';
if (is_file($kernelFilename)) {
    $rector->withSymfonyContainerXml($kernelFilename);
}

return $rector;
