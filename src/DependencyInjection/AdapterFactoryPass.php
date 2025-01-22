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

namespace Mezcalito\UxSearchBundle\DependencyInjection;

use Mezcalito\UxSearchBundle\Adapter\AdapterProvider;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AdapterFactoryPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds('mezcalito_ux_search.adapter_factory');

        $factories = array_map(fn ($fqcn) => new Reference($fqcn), array_keys($taggedServices));

        $container
            ->getDefinition(AdapterProvider::class)
            ->setArgument('$factories', new IteratorArgument($factories))
        ;
    }
}
