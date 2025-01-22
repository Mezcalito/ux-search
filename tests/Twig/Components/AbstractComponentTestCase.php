<?php

/*
 * This file is part of the UxSearch project.
 *
 * (c) Mezcalito (https://www.mezcalito.fr)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mezcalito\UxSearchBundle\Tests\Twig\Components;

use Mezcalito\UxSearchBundle\Context\Context;
use Mezcalito\UxSearchBundle\Context\ContextProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractComponentTestCase extends KernelTestCase
{
    public function setCurrentContext(Context $context): void
    {
        if (!self::$booted) {
            self::bootKernel();
        }

        $container = static::getContainer();
        $containerProvider = $this->createStub(ContextProvider::class);
        $containerProvider->method('getCurrentContext')->willReturn($context);
        $container->set(ContextProvider::class, $containerProvider);
    }
}
