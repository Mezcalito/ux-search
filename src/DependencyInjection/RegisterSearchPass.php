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

use Mezcalito\UxSearchBundle\Search\SearchProvider;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterSearchPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds('mezcalito_ux_search.search');
        $listSearchTypes = array_combine(
            array_map(fn ($attr) => $attr[0]['name'], $taggedServices),
            array_map(fn ($fqcn) => new Reference($fqcn), array_keys($taggedServices))
        );

        $container
            ->getDefinition(SearchProvider::class)
            ->setArgument('$searchs', new IteratorArgument($listSearchTypes))
        ;
    }
}
