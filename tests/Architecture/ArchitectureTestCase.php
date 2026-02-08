<?php

declare(strict_types=1);

namespace App\Tests\Architecture;

use PHPat\Selector\Selector;

abstract class ArchitectureTestCase
{
    /**
     * @return iterable<int, \PHPat\Selector\ClassNamespace>
     */
    protected function miscellaneous(): iterable
    {
        return [
            Selector::inNamespace(namespace: 'App\Shared\Domain'),
            Selector::inNamespace(namespace: 'Doctrine\Common\Collections'),
        ];
    }

    /**
     * @return iterable<int, \PHPat\Selector\ClassNamespace>
     */
    protected function vendors(): iterable
    {
        return [
            Selector::inNamespace(namespace: 'ApiPlatform'),
            Selector::inNamespace(namespace: 'Doctrine'),
            Selector::inNamespace(namespace: 'Psr'),
            Selector::inNamespace(namespace: 'Symfony'),
        ];
    }

    /**
     * @return iterable<int, \PHPat\Selector\ClassNamespace>
     */
    protected function domain(): iterable
    {
        return $this->miscellaneous();
    }

    /**
     * @return iterable<int, \PHPat\Selector\ClassNamespace>
     */
    protected function application(): iterable
    {
        return [
            ...$this->miscellaneous(),
            Selector::inNamespace(namespace: 'App\Shared\Application'),
        ];
    }

    /**
     * @return iterable<int, \PHPat\Selector\ClassNamespace>
     */
    protected function infrastructure(): iterable
    {
        return [
            ...$this->miscellaneous(),
            ...$this->vendors(),
            Selector::inNamespace(namespace: 'App\Shared\Application'),
            Selector::inNamespace(namespace: 'App\Shared\Infrastructure'),
        ];
    }
}
