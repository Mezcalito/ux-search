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

use Mezcalito\UxSearchBundle\DependencyInjection\UrlFormaterPass;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class UrlFormaterPassTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();

        $providerDefinition = new Definition(UrlFormaterProvider::class);
        $container->setDefinition(UrlFormaterProvider::class, $providerDefinition);

        $taggedServiceIds = [
            'app.url_formater_one' => [],
            'app.url_formater_two' => [],
        ];

        foreach (array_keys($taggedServiceIds) as $id) {
            $container->register($id)->addTag('mezcalito_ux_search.url_formater');
        }

        $compilerPass = new UrlFormaterPass();
        $compilerPass->process($container);

        $this->assertTrue($container->hasDefinition(UrlFormaterProvider::class));

        $expectedArgument = new IteratorArgument([
            'app.url_formater_one' => new Reference('app.url_formater_one'),
            'app.url_formater_two' => new Reference('app.url_formater_two'),
        ]);

        $actualArgument = $providerDefinition->getArgument('$formaters');

        $this->assertEquals($expectedArgument, $actualArgument);
    }
}
