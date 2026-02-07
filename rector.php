<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

$rector = RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/migrations',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/utils',
    ])
    ->withRootFiles()
    ->withPhpSets(
        php85: true,
    )
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::INSTANCEOF,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
    ])
    ->withAttributesSets()
    ->withComposerBased(doctrine: true, phpunit: true, symfony: true)
;

if (is_file(__DIR__.'/var/cache/dev/App_KernelDevDebugContainer.php')) {
    $rector->withSymfonyContainerPhp(__DIR__.'/var/cache/dev/App_KernelDevDebugContainer.php');
} elseif (is_file(__DIR__.'/var/cache/test/App_KernelDevDebugContainer.php')) {
    $rector->withSymfonyContainerPhp(__DIR__.'/var/cache/test/App_KernelDevDebugContainer.php');
}

return $rector;
