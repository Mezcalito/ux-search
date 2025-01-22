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

use Mezcalito\UxSearchBundle\DependencyInjection\RegisterSearchPass;
use Mezcalito\UxSearchBundle\Search\SearchProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterSearchPassTest extends TestCase
{
    public function testProcessRegistersTaggedSearchTypes(): void
    {
        $container = new ContainerBuilder();

        $searchType1 = new Definition();
        $searchType1->addTag('mezcalito_ux_search.search', ['name' => 'search_1']);

        $container->setDefinition('search_service_1', $searchType1);

        $searchType2 = new Definition();
        $searchType2->addTag('mezcalito_ux_search.search', ['name' => 'search_2']);

        $container->setDefinition('search_service_2', $searchType2);

        $searchProviderDefinition = new Definition(SearchProvider::class);
        $container->setDefinition(SearchProvider::class, $searchProviderDefinition);

        $compilerPass = new RegisterSearchPass();
        $compilerPass->process($container);

        $updatedDefinition = $container->getDefinition(SearchProvider::class);

        $iteratorArgument = $updatedDefinition->getArgument('$searchs');
        $this->assertInstanceOf(IteratorArgument::class, $iteratorArgument);

        $arguments = $iteratorArgument->getValues();

        $this->assertCount(2, $arguments);
        $this->assertArrayHasKey('search_1', $arguments);
        $this->assertArrayHasKey('search_2', $arguments);
        $this->assertEquals(new Reference('search_service_1'), $arguments['search_1']);
        $this->assertEquals(new Reference('search_service_2'), $arguments['search_2']);
    }

    public function testProcessDoesNothingIfNoTaggedServices(): void
    {
        $container = new ContainerBuilder();

        $searchProviderDefinition = new Definition(SearchProvider::class);
        $container->setDefinition(SearchProvider::class, $searchProviderDefinition);

        $compilerPass = new RegisterSearchPass();
        $compilerPass->process($container);

        $updatedDefinition = $container->getDefinition(SearchProvider::class);

        $argument = $updatedDefinition->getArgument('$searchs');
        $this->assertInstanceOf(IteratorArgument::class, $argument);
        $this->assertEmpty($argument->getValues());
    }
}
