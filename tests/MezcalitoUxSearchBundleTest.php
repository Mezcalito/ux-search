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

namespace Mezcalito\UxSearchBundle\Tests;

use Mezcalito\UxSearchBundle\Adapter\AdapterFactoryInterface;
use Mezcalito\UxSearchBundle\DependencyInjection\AdapterFactoryPass;
use Mezcalito\UxSearchBundle\DependencyInjection\RegisterSearchPass;
use Mezcalito\UxSearchBundle\DependencyInjection\UrlFormaterPass;
use Mezcalito\UxSearchBundle\MezcalitoUxSearchBundle;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MezcalitoUxSearchBundleTest extends TestCase
{
    public function testStringDsnIsNormalized(): void
    {
        $builder = $this->buildContainer([
            'adapters' => [
                'default' => 'meilisearch://key@meilisearch:7700',
            ],
        ]);

        $this->assertSame(
            ['default' => ['dsn' => 'meilisearch://key@meilisearch:7700']],
            $builder->getParameter('mezcalito_ux_search.adapters')
        );
        $this->assertSame('default', $builder->getParameter('mezcalito_ux_search.default_adapter'));
    }

    public function testExplicitAdapterConfiguration(): void
    {
        $builder = $this->buildContainer([
            'default_adapter' => 'products',
            'adapters' => [
                'products' => ['dsn' => 'algolia://key@appId'],
                'orm' => 'doctrine://default',
            ],
        ]);

        $this->assertSame('products', $builder->getParameter('mezcalito_ux_search.default_adapter'));
        $this->assertSame(
            [
                'products' => ['dsn' => 'algolia://key@appId'],
                'orm' => ['dsn' => 'doctrine://default'],
            ],
            $builder->getParameter('mezcalito_ux_search.adapters')
        );
    }

    public function testAdaptersAreRequired(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->buildContainer([]);
    }

    public function testDsnIsRequired(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->buildContainer(['adapters' => ['default' => []]]);
    }

    public function testAutoconfiguration(): void
    {
        $builder = $this->buildContainer(['adapters' => ['default' => 'doctrine://default']]);

        $autoconfigured = $builder->getAutoconfiguredInstanceof();

        $this->assertArrayHasKey(AdapterFactoryInterface::class, $autoconfigured);
        $this->assertArrayHasKey('mezcalito_ux_search.adapter_factory', $autoconfigured[AdapterFactoryInterface::class]->getTags());
        $this->assertArrayHasKey(UrlFormaterInterface::class, $autoconfigured);
        $this->assertArrayHasKey('mezcalito_ux_search.url_formater', $autoconfigured[UrlFormaterInterface::class]->getTags());
    }

    public function testBuildRegistersCompilerPasses(): void
    {
        $builder = new ContainerBuilder();

        (new MezcalitoUxSearchBundle())->build($builder);

        $passClasses = array_map(get_class(...), $builder->getCompilerPassConfig()->getBeforeOptimizationPasses());

        $this->assertContains(RegisterSearchPass::class, $passClasses);
        $this->assertContains(AdapterFactoryPass::class, $passClasses);
        $this->assertContains(UrlFormaterPass::class, $passClasses);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function buildContainer(array $config): ContainerBuilder
    {
        $builder = new ContainerBuilder();
        $builder->setParameter('kernel.debug', false);
        $builder->setParameter('kernel.environment', 'test');
        $builder->setParameter('kernel.build_dir', sys_get_temp_dir());

        $bundle = new MezcalitoUxSearchBundle();
        $extension = $bundle->getContainerExtension();
        $extension->load([$config], $builder);

        return $builder;
    }
}
