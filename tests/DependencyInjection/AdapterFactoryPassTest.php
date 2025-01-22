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

namespace Mezcalito\UxSearchBundle\Tests\DependencyInjection;

use Mezcalito\UxSearchBundle\Adapter\AdapterProvider;
use Mezcalito\UxSearchBundle\DependencyInjection\AdapterFactoryPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AdapterFactoryPassTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();

        $container->register('factory_1')
            ->addTag('mezcalito_ux_search.adapter_factory');
        $container->register('factory_2')
            ->addTag('mezcalito_ux_search.adapter_factory');

        $container->register(AdapterProvider::class)
            ->setArgument('$defaultAdapterName', 'default')
            ->setArgument('$adapterConfiguration', [])
            ->setArgument('$factories', []);

        $compilerPass = new AdapterFactoryPass();
        $compilerPass->process($container);

        $definition = $container->getDefinition(AdapterProvider::class);

        $factoriesArgument = $definition->getArgument('$factories');

        $this->assertInstanceOf(IteratorArgument::class, $factoriesArgument);

        $factories = $factoriesArgument->getValues();
        $this->assertCount(2, $factories);
        $this->assertContainsOnlyInstancesOf(Reference::class, $factories);
        $this->assertEquals('factory_1', (string) $factories[0]);
        $this->assertEquals('factory_2', (string) $factories[1]);
    }
}
